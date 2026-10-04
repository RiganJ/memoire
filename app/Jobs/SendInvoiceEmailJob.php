<?php

namespace App\Jobs;

use App\Mail\PaymentInvoiceMail;
use App\Models\Invoice;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendInvoiceEmailJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [30, 120, 300];

    public function __construct(public int $invoiceId)
    {
        $this->afterCommit();
    }

    public function handle(): void
    {
        $invoice = Invoice::query()
            ->with('order.catalog', 'order.servicePackage', 'payment.paymentMethod')
            ->findOrFail($this->invoiceId);

        if ($invoice->email_status === 'sent') {
            return;
        }

        $invoice->increment('email_attempts');
        Mail::to($invoice->customer_email)->send(new PaymentInvoiceMail($invoice));
        $invoice->update(['email_status' => 'sent', 'email_sent_at' => now()]);
    }

    public function uniqueId(): string
    {
        return (string) $this->invoiceId;
    }

    public function failed(?Throwable $exception): void
    {
        Invoice::query()->whereKey($this->invoiceId)->update(['email_status' => 'failed']);
    }
}
