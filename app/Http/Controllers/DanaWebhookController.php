<?php

namespace App\Http\Controllers;

use App\Contracts\DanaQrisGateway;
use App\Jobs\SendInvoiceEmailJob;
use App\Models\Payment;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class DanaWebhookController extends Controller
{
    public function __invoke(Request $request, DanaQrisGateway $gateway, InvoiceService $invoices): JsonResponse
    {
        try {
            $notification = $gateway->parseWebhook(
                $request->method(),
                '/v1.0/debit/notify',
                collect($request->headers->all())->map(fn (array $values): string => implode(',', $values))->all(),
                $request->getContent(),
            );
        } catch (Throwable $exception) {
            Log::warning('Invalid DANA webhook.', ['error_category' => $exception::class]);

            return response()->json(['responseCode' => '4015600', 'responseMessage' => 'Unauthorized'], 401);
        }

        try {
            DB::transaction(function () use ($notification, $invoices): void {
                $payment = Payment::query()
                    ->where('partner_reference_no', $notification->partnerReferenceNo)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! hash_equals((string) config('dana.merchant_id'), $notification->merchantId)) {
                    abort(422, 'Merchant mismatch.');
                }

                $storedAmount = (int) round((float) $payment->amount * 100);
                $notifiedAmount = (int) round((float) $notification->amount * 100);
                if ($payment->currency !== $notification->currency || $storedAmount !== $notifiedAmount) {
                    abort(422, 'Payment amount mismatch.');
                }

                if ($payment->dana_reference_no !== null && $notification->referenceNo !== null
                    && ! hash_equals($payment->dana_reference_no, $notification->referenceNo)) {
                    abort(422, 'DANA reference mismatch.');
                }

                if ($payment->status === 'paid') {
                    return;
                }

                if ($notification->status === '05') {
                    $payment->update(['status' => 'expired', 'failed_at' => $notification->finishedAt ?? now()]);

                    return;
                }

                if ($notification->status !== '00') {
                    return;
                }

                $payment->update([
                    'status' => 'paid',
                    'dana_reference_no' => $notification->referenceNo,
                    'paid_at' => $notification->finishedAt ?? now(),
                ]);
                $payment->order()->update(['status' => 'paid', 'payment_status' => 'paid']);
                $payment->order->customer?->increment('total_spent', (float) $payment->amount);

                $invoice = $invoices->createForPayment($payment->load('order', 'servicePackage'));
                DB::afterCommit(fn () => SendInvoiceEmailJob::dispatch($invoice->id));
            });
        } catch (Throwable $exception) {
            Log::warning('DANA webhook rejected.', [
                'partner_reference' => $notification->partnerReferenceNo,
                'error_category' => $exception::class,
            ]);

            return response()->json(['responseCode' => '4005600', 'responseMessage' => 'Bad Request'], 422);
        }

        return response()->json(['responseCode' => '2005600', 'responseMessage' => 'Successful']);
    }
}
