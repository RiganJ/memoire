<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendInvoiceEmailJob;
use App\Models\Payment;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;

class PaymentInvoiceEmailController extends Controller
{
    public function __invoke(Payment $payment, InvoiceService $invoices): RedirectResponse
    {
        if (! in_array($payment->status, ['success', 'paid'], true)) {
            return back()->withErrors(['invoice' => 'Invoice hanya dapat dikirim setelah pembayaran berhasil.']);
        }

        $payment->load('order.servicePackage', 'servicePackage', 'paymentMethod', 'invoice');
        if ($payment->paymentMethod->code === 'dana' && $payment->partner_reference_no !== null) {
            return back()->withErrors(['invoice' => 'Invoice QRIS dikirim otomatis setelah webhook pembayaran berhasil.']);
        }

        $invoice = $payment->invoice ?? $invoices->createForPayment($payment);
        if ($invoice->email_status === 'sent') {
            return back()->with('success', 'Invoice sudah pernah dikirim ke '.$invoice->customer_email.'.');
        }

        $invoice->update(['email_status' => 'pending']);
        SendInvoiceEmailJob::dispatch($invoice->id);

        return back()->with('success', 'Invoice dijadwalkan untuk dikirim ke '.$invoice->customer_email.'.');
    }
}
