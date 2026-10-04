<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $invoice) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Pembayaran Berhasil — Invoice '.$this->invoice->invoice_number.' | Memoire');
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.invoices.payment');
    }

    /** @return array<int, mixed> */
    public function attachments(): array
    {
        return [];
    }
}
