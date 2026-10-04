@extends('customer.layouts.app')
@section('title', 'Dashboard Pesanan')
@section('content')
    @php($invoice = $order->payments->sortByDesc('paid_at')->first()?->invoice)
    <section class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-[10px] font-bold uppercase tracking-[.2em] text-[#bd9150]">Pesanan Anda</p><h1 class="mt-2 font-display text-4xl text-[#582308] sm:text-5xl">Halo, {{ $order->customer_name }}</h1><p class="mt-2 text-sm text-[#32170b]/45">Pembayaran berhasil. Tim Memoire akan melanjutkan proses undangan Anda.</p></div>
        @if ($invoice)<a href="{{ route('public.invoices.show', $invoice) }}" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-[#582308] px-5 text-sm font-semibold text-white"><i class="fa-regular fa-file-lines"></i>Lihat Invoice</a>@endif
    </section>
    <section class="mt-8 grid gap-5 md:grid-cols-3">
        <article class="rounded-3xl border border-[#582308]/8 bg-white p-6"><p class="text-[9px] font-bold uppercase tracking-[.18em] text-[#32170b]/35">Nomor pesanan</p><p class="mt-3 font-display text-2xl text-[#582308]">{{ $order->order_number }}</p></article>
        <article class="rounded-3xl border border-[#582308]/8 bg-white p-6"><p class="text-[9px] font-bold uppercase tracking-[.18em] text-[#32170b]/35">Paket</p><p class="mt-3 font-display text-2xl text-[#582308]">{{ $order->servicePackage?->name ?? $order->package }}</p></article>
        <article class="rounded-3xl border border-[#582308]/8 bg-white p-6"><p class="text-[9px] font-bold uppercase tracking-[.18em] text-[#32170b]/35">Status</p><p class="mt-3 font-display text-2xl capitalize text-[#582308]">{{ $order->status }}</p></article>
    </section>
    <section class="mt-6 rounded-3xl bg-[#582308] p-7 text-white"><p class="text-[9px] font-bold uppercase tracking-[.2em] text-[#ead5ac]/65">Kode akses dashboard</p><p class="mt-3 font-mono text-2xl font-bold tracking-[.12em] text-[#ead5ac]">{{ $order->customer_access_code }}</p><p class="mt-3 text-xs leading-6 text-white/50">Simpan kode ini. Kode hanya aktif selama pesanan memenuhi akses paket dan pembayarannya berstatus lunas.</p></section>
@endsection
