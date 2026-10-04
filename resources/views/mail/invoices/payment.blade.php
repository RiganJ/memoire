<x-mail::message>
<div style="text-align: center; margin-bottom: 24px;">
<img src="{{ asset('images/logo-memoire.png') }}" width="96" height="96" alt="Memoire" style="display: inline-block; object-fit: contain;">
</div>

# Pembayaran Berhasil

Halo {{ $invoice->customer_name }},

Terima kasih telah mempercayakan momen Anda kepada Memoire. Pembayaran untuk pesanan **{{ $invoice->order->order_number }}** telah berhasil kami terima.

<x-mail::panel>
**Nomor Invoice:** {{ $invoice->invoice_number }}<br>
**Desain:** {{ $invoice->order->catalog?->name ?? 'Desain Memoire' }}<br>
**Kategori:** {{ $invoice->order->form_category ?: ($invoice->order->event_type ?: '—') }}<br>
**Paket:** {{ $invoice->package_name }}<br>
**Total:** Rp {{ number_format((float) $invoice->amount, 0, ',', '.') }}<br>
**Metode:** {{ $invoice->payment->paymentMethod?->name ?? 'DANA QRIS' }}<br>
**Status:** LUNAS<br>
**Tanggal Pembayaran:** {{ $invoice->paid_at->translatedFormat('d F Y, H.i') }} WIB
</x-mail::panel>

<x-mail::button :url="route('public.invoices.show', $invoice)">
Lihat Detail Invoice
</x-mail::button>

Invoice lengkap memuat detail pesanan dan personalisasi yang Anda kirimkan.

Salam hangat,<br>
**Memoire — A home for moments**
</x-mail::message>
