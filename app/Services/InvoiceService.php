<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Str;

class InvoiceService
{
    public function createForPayment(Payment $payment): Invoice
    {
        return Invoice::query()->firstOrCreate(
            ['payment_id' => $payment->id],
            [
                'uuid' => (string) Str::uuid(),
                'invoice_number' => $this->nextNumber(),
                'order_id' => $payment->order_id,
                'customer_name' => $payment->order->customer_name,
                'customer_email' => $payment->order->email,
                'package_name' => $payment->servicePackage?->name ?? $payment->order->package,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'paid_at' => $payment->paid_at,
                'email_status' => 'pending',
            ],
        );
    }

    private function nextNumber(): string
    {
        do {
            $number = 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Invoice::query()->where('invoice_number', $number)->exists());

        return $number;
    }
}
