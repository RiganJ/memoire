<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{{ $payment->transaction_number }} — Memoire Admin</title>@include('partials.favicon')@vite('resources/css/app.css')</head>
<body class="bg-[#f4efe7] text-[#32170b]">
@php
    $statusLabels = ['pending' => 'Menunggu', 'success' => 'Berhasil', 'paid' => 'Lunas', 'failed' => 'Gagal', 'expired' => 'Kedaluwarsa', 'cancelled' => 'Dibatalkan', 'refunded' => 'Dikembalikan'];
    $isQris = $payment->paymentMethod->code === 'dana' && $payment->partner_reference_no !== null;
@endphp
<div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
    @include('admin.partials.sidebar')
    <main>
        <header class="flex items-center justify-between border-b border-[#582308]/10 px-5 py-7 sm:px-8 lg:px-10"><div><a href="{{ route('admin.payments.index') }}" class="text-xs text-[#32170b]/45"><i class="fa-solid fa-arrow-left mr-2"></i>Kembali</a><h1 class="mt-3 font-display text-3xl text-[#582308]">{{ $payment->transaction_number }}</h1></div><a href="{{ route('admin.payments.edit', $payment) }}" class="rounded-full bg-[#582308] px-5 py-3 text-sm font-semibold text-white">Edit transaksi</a></header>
        <div class="mx-auto max-w-5xl p-5 sm:p-8 lg:p-10">
            @if(session('success'))<div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">{{ session('success') }}</div>@endif
            @if($errors->has('invoice'))<div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">{{ $errors->first('invoice') }}</div>@endif
            <div class="grid gap-6 lg:grid-cols-[1fr_.65fr]">
                <section class="rounded-3xl border border-[#582308]/8 bg-white p-6 sm:p-8">
                    <p class="text-[10px] font-bold uppercase tracking-[.2em] text-[#32170b]/40">Informasi transaksi</p>
                    <dl class="mt-6 grid gap-6 sm:grid-cols-2">@foreach([['Nomor pesanan', $payment->order->order_number], ['Pelanggan', $payment->order->customer_name], ['Metode', $payment->paymentMethod->name], ['Akun tujuan', $payment->paymentMethod->account_number], ['Tanggal', ($payment->paid_at ?? $payment->created_at)->translatedFormat('d F Y, H.i')], ['Status', $statusLabels[$payment->status] ?? ucfirst($payment->status)]] as [$term, $value])<div><dt class="text-[9px] font-bold uppercase tracking-[.15em] text-[#32170b]/35">{{ $term }}</dt><dd class="mt-2 text-sm font-semibold text-[#32170b]/75">{{ $value }}</dd></div>@endforeach</dl>
                    @if($payment->notes)<div class="mt-7 border-t border-[#582308]/8 pt-6"><p class="text-[9px] font-bold uppercase tracking-[.15em] text-[#32170b]/35">Catatan</p><p class="mt-2 text-sm leading-7 text-[#32170b]/65">{{ $payment->notes }}</p></div>@endif
                </section>
                <aside class="space-y-5">
                    <section class="rounded-3xl bg-[#582308] p-6 text-white"><p class="text-[9px] font-bold uppercase tracking-[.2em] text-[#ead5ac]/70">Nominal</p><p class="mt-3 font-display text-3xl text-[#ead5ac]">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p></section>
                    <section class="rounded-3xl border border-[#582308]/8 bg-white p-6">
                        <div class="flex items-start justify-between gap-3"><div><p class="text-[9px] font-bold uppercase tracking-[.18em] text-[#bd9150]">Invoice email</p><h2 class="mt-2 font-display text-xl text-[#582308]">{{ $payment->invoice?->invoice_number ?? 'Belum tersedia' }}</h2></div>@if($payment->invoice)<span class="rounded-full px-2.5 py-1 text-[9px] font-bold {{ $payment->invoice->email_status === 'sent' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $payment->invoice->email_status === 'sent' ? 'Terkirim' : 'Belum dikirim' }}</span>@endif</div>
                        @if($payment->invoice)
                            <a href="{{ route('public.invoices.show', $payment->invoice) }}" target="_blank" rel="noopener" class="mt-5 flex h-11 items-center justify-center gap-2 rounded-xl border border-[#582308]/12 text-xs font-semibold text-[#582308]"><i class="fa-regular fa-file-lines"></i>Lihat Invoice</a>
                            @if($isQris)
                                <p class="mt-3 rounded-xl bg-[#f7f0e5] p-3 text-[10px] leading-5 text-[#32170b]/55"><i class="fa-solid fa-bolt mr-1 text-[#bd9150]"></i>Invoice QRIS dikirim otomatis setelah pembayaran dikonfirmasi DANA.</p>
                            @elseif($payment->invoice->email_status !== 'sent')
                                <form method="POST" action="{{ route('admin.payments.send-invoice', $payment) }}" class="mt-3">@csrf<button class="flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-[#582308] text-xs font-semibold text-white"><i class="fa-regular fa-paper-plane"></i>Kirim Invoice ke Email</button></form>
                                <p class="mt-2 text-center text-[10px] text-[#32170b]/40">Email hanya dikirim setelah tombol ditekan admin.</p>
                            @endif
                        @elseif(!in_array($payment->status, ['success', 'paid'], true))
                            <p class="mt-4 text-xs leading-5 text-[#32170b]/45">Invoice tersedia setelah pembayaran ditandai berhasil atau lunas.</p>
                        @endif
                    </section>
                </aside>
            </div>
        </div>
    </main>
</div>
</body>
</html>
