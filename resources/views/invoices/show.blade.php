<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $invoice->invoice_number }} — Memoire</title>
    @include('partials.favicon')
    @vite('resources/css/app.css')
    <style>
        @media print {
            @page { size: A4; margin: 12mm; }
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body class="min-h-screen bg-[#efe8dc] px-4 py-8 text-[#32170b] antialiased sm:px-6 sm:py-12 print:bg-white print:p-0">
    @php
        $order = $invoice->order;
        $payment = $invoice->payment;
        $formDetails = collect($order->form_data ?? [])->filter(fn ($value) => filled($value));
    @endphp

    <main class="relative mx-auto max-w-4xl overflow-hidden rounded-[2rem] bg-[#fffdf9] shadow-[0_28px_90px_rgba(67,29,10,.16)] print:rounded-none print:shadow-none">
        <div class="absolute inset-x-0 top-0 h-2 bg-gradient-to-r from-[#582308] via-[#bd9150] to-[#582308]"></div>
        <div class="pointer-events-none absolute -right-24 -top-24 size-72 rounded-full border border-[#bd9150]/20"></div>
        <div class="pointer-events-none absolute -right-10 -top-10 size-44 rounded-full border border-[#bd9150]/20"></div>

        <header class="relative bg-[#582308] px-6 pb-10 pt-12 text-[#fffaf0] sm:px-12">
            <div class="flex flex-col justify-between gap-8 sm:flex-row sm:items-start">
                <div class="flex items-center gap-5">
                    <span class="grid size-24 shrink-0 place-items-center rounded-full border border-[#ead5ac]/35 bg-white/95 shadow-lg">
                        <img src="{{ asset('images/logo-memoire.png') }}" alt="Logo Memoire" class="size-20 object-contain">
                    </span>
                    <div><p class="font-display text-3xl tracking-[.16em] text-[#ead5ac]">MEMOIRE</p><p class="mt-1 text-[9px] uppercase tracking-[.3em] text-[#fffaf0]/60">A home for moments</p></div>
                </div>
                <div class="sm:text-right">
                    <p class="text-[10px] font-semibold uppercase tracking-[.32em] text-[#ead5ac]">Invoice pembayaran</p>
                    <h1 class="mt-3 font-display text-3xl sm:text-4xl">{{ $invoice->invoice_number }}</h1>
                    <span class="mt-4 inline-flex items-center gap-2 rounded-full border border-emerald-300/25 bg-emerald-400/10 px-4 py-2 text-[10px] font-bold uppercase tracking-[.18em] text-emerald-200"><span class="size-1.5 rounded-full bg-emerald-300"></span>Lunas</span>
                </div>
            </div>
        </header>

        <div class="relative px-6 py-9 sm:px-12 sm:py-12">
            <section class="grid gap-5 sm:grid-cols-3">
                <div class="rounded-2xl border border-[#582308]/10 bg-[#faf6ef] p-5 sm:col-span-2">
                    <p class="text-[9px] font-bold uppercase tracking-[.22em] text-[#bd9150]">Ditagihkan kepada</p>
                    <h2 class="mt-3 font-display text-2xl text-[#582308]">{{ $invoice->customer_name }}</h2>
                    <div class="mt-3 space-y-1 text-sm text-[#32170b]/60"><p>{{ $invoice->customer_email }}</p>@if ($order->phone)<p>{{ $order->phone }}</p>@endif</div>
                </div>
                <div class="rounded-2xl border border-[#582308]/10 bg-white p-5">
                    <p class="text-[9px] font-bold uppercase tracking-[.22em] text-[#bd9150]">Tanggal pembayaran</p>
                    <p class="mt-3 font-display text-xl text-[#582308]">{{ $invoice->paid_at->translatedFormat('d F Y') }}</p>
                    <p class="mt-1 text-xs text-[#32170b]/50">{{ $invoice->paid_at->translatedFormat('H.i') }} WIB</p>
                </div>
            </section>

            <section class="mt-8">
                <div class="mb-4 flex items-end justify-between gap-4">
                    <div><p class="text-[9px] font-bold uppercase tracking-[.22em] text-[#bd9150]">Rincian pesanan</p><h2 class="mt-2 font-display text-2xl text-[#582308]">Kisah yang Anda pilih</h2></div>
                    <p class="text-right text-[10px] text-[#32170b]/45">Pesanan<br><b class="text-[#582308]">{{ $order->order_number }}</b></p>
                </div>
                <dl class="grid overflow-hidden rounded-2xl border border-[#582308]/10 sm:grid-cols-2">
                    <div class="border-b border-[#582308]/10 bg-[#faf6ef] px-5 py-4 sm:border-r"><dt class="text-[9px] uppercase tracking-[.16em] text-[#32170b]/40">Paket</dt><dd class="mt-2 font-semibold text-[#582308]">{{ $invoice->package_name }}</dd></div>
                    <div class="border-b border-[#582308]/10 px-5 py-4"><dt class="text-[9px] uppercase tracking-[.16em] text-[#32170b]/40">Desain katalog</dt><dd class="mt-2 font-semibold text-[#582308]">{{ $order->catalog?->name ?? 'Desain Memoire' }}</dd></div>
                    <div class="border-b border-[#582308]/10 px-5 py-4 sm:border-b-0 sm:border-r"><dt class="text-[9px] uppercase tracking-[.16em] text-[#32170b]/40">Kategori</dt><dd class="mt-2 text-sm">{{ $order->form_category ?: ($order->event_type ?: '—') }}</dd></div>
                    <div class="px-5 py-4"><dt class="text-[9px] uppercase tracking-[.16em] text-[#32170b]/40">Tanggal acara</dt><dd class="mt-2 text-sm">{{ $order->event_date?->translatedFormat('d F Y') ?? 'Belum ditentukan' }}</dd></div>
                </dl>
            </section>

            @if ($formDetails->isNotEmpty() || $order->notes)
                <section class="mt-8 rounded-2xl border border-[#bd9150]/25 bg-[#fbf6eb] p-5 sm:p-6">
                    <p class="text-[9px] font-bold uppercase tracking-[.22em] text-[#bd9150]">Detail cerita & personalisasi</p>
                    @if ($formDetails->isNotEmpty())
                        <dl class="mt-5 grid gap-x-8 gap-y-5 sm:grid-cols-2">
                            @foreach ($formDetails as $label => $value)
                                <div><dt class="text-[10px] font-semibold uppercase tracking-[.12em] text-[#32170b]/40">{{ str($label)->replace('_', ' ')->title() }}</dt><dd class="mt-1 whitespace-pre-line text-sm leading-6 text-[#32170b]/75">{{ is_array($value) ? implode(', ', $value) : $value }}</dd></div>
                            @endforeach
                        </dl>
                    @endif
                    @if ($order->notes)
                        <div class="mt-5 border-t border-[#582308]/10 pt-5"><p class="text-[10px] font-semibold uppercase tracking-[.12em] text-[#32170b]/40">Catatan tambahan</p><p class="mt-2 whitespace-pre-line text-sm leading-6 text-[#32170b]/75">{{ $order->notes }}</p></div>
                    @endif
                </section>
            @endif

            <section class="mt-8 overflow-hidden rounded-2xl border border-[#582308]/10">
                <div class="grid grid-cols-[1fr_auto] bg-[#f8f2e8] px-5 py-3 text-[9px] font-bold uppercase tracking-[.18em] text-[#32170b]/45"><span>Deskripsi</span><span>Jumlah</span></div>
                <div class="grid grid-cols-[1fr_auto] items-center gap-6 px-5 py-6"><div><p class="font-semibold text-[#582308]">Paket {{ $invoice->package_name }}</p><p class="mt-1 text-xs text-[#32170b]/45">1 × layanan undangan digital Memoire</p></div><p class="font-semibold">Rp {{ number_format((float) $invoice->amount, 0, ',', '.') }}</p></div>
                <div class="grid grid-cols-[1fr_auto] items-center gap-6 bg-[#582308] px-5 py-5 text-white"><div><p class="text-[9px] uppercase tracking-[.18em] text-white/55">Total dibayar</p><p class="mt-1 text-xs text-[#ead5ac]">{{ $invoice->currency }} · {{ $payment->paymentMethod?->name ?? 'DANA QRIS' }}</p></div><strong class="font-display text-2xl text-[#ead5ac]">Rp {{ number_format((float) $invoice->amount, 0, ',', '.') }}</strong></div>
            </section>

            <section class="mt-6 grid gap-4 rounded-2xl border border-dashed border-[#582308]/15 px-5 py-4 text-xs text-[#32170b]/55 sm:grid-cols-2">
                <p><span class="block text-[9px] uppercase tracking-[.15em] text-[#32170b]/35">Referensi pembayaran</span><b class="mt-1 block break-all text-[#582308]">{{ $payment->dana_reference_no ?: $payment->partner_reference_no }}</b></p>
                <p class="sm:text-right"><span class="block text-[9px] uppercase tracking-[.15em] text-[#32170b]/35">Status pesanan</span><b class="mt-1 block capitalize text-[#582308]">{{ $order->status }}</b></p>
            </section>

            <footer class="mt-10 flex flex-col items-center justify-between gap-5 border-t border-[#582308]/10 pt-7 text-center sm:flex-row sm:text-left">
                <div><p class="font-display text-lg text-[#582308]">Terima kasih telah mempercayakan momen Anda.</p><p class="mt-1 text-xs text-[#32170b]/45">Simpan invoice ini sebagai bukti pembayaran resmi Memoire.</p></div>
                <button type="button" onclick="window.print()" class="shrink-0 rounded-full bg-[#582308] px-6 py-3 text-xs font-semibold text-white shadow-lg transition hover:bg-[#6f3212] print:hidden"><i class="fa-solid fa-print mr-2"></i>Cetak / Simpan PDF</button>
            </footer>
        </div>
    </main>
</body>
</html>
