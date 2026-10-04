<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;

class PaymentInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $invoice)
    {
        $this->withSymfonyMessage(function (Email $message): void {
            $logo = (new DataPart(new File(public_path('images/logo-memoire.png')), 'logo-memoire.png', 'image/png'))
                ->asInline()
                ->setContentId('logo-memoire@memoire');

            $message->addPart($logo);
        });
    }

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
