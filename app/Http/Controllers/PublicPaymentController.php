<?php

namespace App\Http\Controllers;

use App\Contracts\DanaQrisGateway;
use App\Exceptions\DanaConfigurationException;
use App\Http\Requests\GenerateDanaQrisRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class PublicPaymentController extends Controller
{
    public function store(GenerateDanaQrisRequest $request, DanaQrisGateway $gateway): View
    {
        $payment = DB::transaction(function () use ($request): Payment {
            $order = Order::query()->where('uuid', $request->validated('order_uuid'))->lockForUpdate()->firstOrFail();
            abort_if($order->payment_status === 'paid', 409, 'Pesanan ini sudah dibayar.');

            $existing = $order->payments()
                ->where('status', 'pending')
                ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->latest('id')
                ->first();

            if ($existing !== null) {
                return $existing;
            }

            $method = PaymentMethod::query()->where('code', 'dana')->firstOrFail();

            return Payment::create([
                'uuid' => (string) Str::uuid(),
                'order_id' => $order->id,
                'payment_method_id' => $method->id,
                'service_package_id' => $order->service_package_id,
                'transaction_number' => 'PAY-'.Str::upper(Str::random(12)),
                'partner_reference_no' => 'MEM'.now()->format('ymd').Str::upper(Str::random(16)),
                'amount' => $order->servicePackage?->price ?? $order->total,
                'currency' => 'IDR',
                'status' => 'pending',
                'expires_at' => now()->addMinutes(15),
            ]);
        });

        if ($payment->qr_content === null) {
            try {
                $result = $gateway->generate($payment->loadMissing('order', 'servicePackage'));
                $payment->update([
                    'dana_reference_no' => $result->referenceNo ?: null,
                    'qr_content' => $result->qrContent,
                    'qr_url' => $result->qrUrl,
                    'qr_image' => $result->qrImage,
                ]);
            } catch (DanaConfigurationException $exception) {
                return view('payments.error', ['message' => $exception->getMessage()]);
            } catch (Throwable $exception) {
                Log::error('DANA QRIS generation failed.', [
                    'payment_uuid' => $payment->uuid,
                    'partner_reference' => $payment->partner_reference_no,
                    'error_category' => $exception::class,
                ]);

                return view('payments.error', ['message' => 'QRIS belum berhasil dibuat. Silakan tutup tab ini dan coba kembali.']);
            }
        }

        return view('payments.show', ['payment' => $payment->refresh()->load('order', 'servicePackage', 'invoice')]);
    }

    public function show(Payment $payment): View
    {
        abort_if($payment->uuid === null, 404);

        return view('payments.show', ['payment' => $payment->load('order', 'servicePackage', 'invoice')]);
    }

    public function status(Payment $payment): JsonResponse
    {
        $payment->load('invoice');

        return response()->json([
            'status' => $payment->status,
            'invoice_available' => $payment->invoice !== null,
            'invoice_number' => $payment->invoice?->invoice_number,
            'invoice_url' => $payment->invoice ? route('public.invoices.show', $payment->invoice) : null,
        ]);
    }
}
