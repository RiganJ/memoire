<?php

namespace App\Http\Controllers;

use App\Contracts\DanaQrisGateway;
use App\Exceptions\DanaConfigurationException;
use App\Http\Requests\GenerateDanaQrisRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class PublicPaymentController extends Controller
{
    public function submitProof(Request $request, Order $order): JsonResponse
    {
        $data = $request->validate([
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);
        $paymentMethod = PaymentMethod::query()
            ->whereKey($data['payment_method_id'])
            ->where('is_active', true)
            ->where('code', '!=', 'dana')
            ->first();

        if ($paymentMethod === null) {
            throw ValidationException::withMessages([
                'payment_method_id' => 'Pilih metode transfer yang masih aktif.',
            ]);
        }

        abort_if($order->payment_status === 'paid', 409, 'Pesanan ini sudah lunas.');

        $proofPath = $request->file('proof')->store('payment-proofs', 'local');

        if (! is_string($proofPath)) {
            throw new \RuntimeException('Bukti pembayaran gagal disimpan.');
        }

        $previousProofPath = null;
        try {
            $payment = DB::transaction(function () use ($order, $paymentMethod, $proofPath, &$previousProofPath): Payment {
                $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
                abort_if($lockedOrder->payment_status === 'paid', 409, 'Pesanan ini sudah lunas.');

                $payment = $lockedOrder->payments()
                    ->where('payment_method_id', $paymentMethod->id)
                    ->where('status', 'pending')
                    ->latest('id')
                    ->first();

                if ($payment !== null) {
                    $previousProofPath = $payment->proof_path;
                    $payment->update([
                        'proof_path' => $proofPath,
                        'proof_submitted_at' => now(),
                        'amount' => $lockedOrder->total,
                    ]);

                    return $payment;
                }

                return Payment::query()->create([
                    'uuid' => (string) Str::uuid(),
                    'order_id' => $lockedOrder->id,
                    'payment_method_id' => $paymentMethod->id,
                    'service_package_id' => $lockedOrder->service_package_id,
                    'transaction_number' => 'PAY-'.Str::upper(Str::random(12)),
                    'amount' => $lockedOrder->total,
                    'currency' => 'IDR',
                    'status' => 'pending',
                    'proof_path' => $proofPath,
                    'proof_submitted_at' => now(),
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($proofPath);

            throw $exception;
        }

        if ($previousProofPath !== null && $previousProofPath !== $proofPath) {
            Storage::disk('local')->delete($previousProofPath);
        }

        return response()->json([
            'message' => 'Bukti pembayaran berhasil dikirim dan menunggu verifikasi admin.',
            'payment' => [
                'transaction_number' => $payment->transaction_number,
                'status' => $payment->status,
                'proof_submitted_at' => $payment->proof_submitted_at?->toIso8601String(),
            ],
        ], 201);
    }

    public function store(GenerateDanaQrisRequest $request, DanaQrisGateway $gateway): View|Response
    {
        $method = PaymentMethod::query()->where('code', 'dana')->where('is_active', true)->first();
        if ($method === null) {
            return response()->view('payments.error', [
                'message' => 'Pembayaran QRIS sedang dinonaktifkan. Silakan pilih metode pembayaran lain atau hubungi admin.',
            ], 503);
        }

        $payment = DB::transaction(function () use ($request, $method): Payment {
            $order = Order::query()->where('uuid', $request->validated('order_uuid'))->lockForUpdate()->firstOrFail();
            abort_if($order->payment_status === 'paid', 409, 'Pesanan ini sudah dibayar.');

            $existing = $order->payments()
                ->where('payment_method_id', $method->id)
                ->where('status', 'pending')
                ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->latest('id')
                ->first();

            if ($existing !== null) {
                return $existing;
            }

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
