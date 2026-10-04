<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Pembayaran {{ $payment->order->order_number }} — Memoire</title>
    @include('partials.favicon')
    @vite('resources/css/app.css')
    @if ($payment->status === 'pending' && $payment->qr_content)
        <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.4/build/qrcode.min.js" defer></script>
    @endif
</head>
<body class="min-h-screen bg-[#f6f1e8] px-5 py-10 text-[#32170b] antialiased">
    <main class="mx-auto max-w-2xl overflow-hidden rounded-[2rem] border border-[#582308]/10 bg-white shadow-[0_25px_80px_rgba(88,35,8,.12)]">
        <header class="bg-[#582308] px-7 py-8 text-center text-white">
            <img src="{{ asset('images/logo-memoire.png') }}" alt="Logo Memoire" class="mx-auto h-20 w-20 object-contain">
            <p class="mt-3 text-[10px] uppercase tracking-[.3em] text-[#ead5ac]">A home for moments</p>
            <h1 class="mt-3 font-display text-3xl">Detail Pembayaran</h1>
        </header>
        <div class="p-6 sm:p-9">
            <dl class="grid gap-5 rounded-2xl bg-[#faf7f0] p-5 sm:grid-cols-2">
                <div><dt class="text-[10px] uppercase tracking-wider text-[#32170b]/40">Nomor Pesanan</dt><dd class="mt-1 font-semibold">{{ $payment->order->order_number }}</dd></div>
                <div><dt class="text-[10px] uppercase tracking-wider text-[#32170b]/40">Nama Pemesan</dt><dd class="mt-1 font-semibold">{{ $payment->order->customer_name }}</dd></div>
                <div><dt class="text-[10px] uppercase tracking-wider text-[#32170b]/40">Paket</dt><dd class="mt-1 font-semibold">{{ $payment->servicePackage?->name ?? $payment->order->package }}</dd></div>
                <div><dt class="text-[10px] uppercase tracking-wider text-[#32170b]/40">Total Pembayaran</dt><dd class="mt-1 text-xl font-bold text-[#582308]">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</dd></div>
            </dl>

            <section id="pending-state" class="{{ $payment->status === 'paid' ? 'hidden' : '' }} mt-8 text-center">
                <h2 class="font-display text-2xl text-[#582308]">Scan QRIS</h2>
                <p class="mt-2 text-sm text-[#32170b]/55">Gunakan aplikasi pembayaran yang mendukung QRIS.</p>
                <div class="mx-auto mt-6 grid min-h-64 w-64 place-items-center rounded-3xl border border-[#582308]/10 bg-white p-4 shadow-inner">
                    <canvas id="qris-code" class="max-w-full" aria-label="QRIS pembayaran"></canvas>
                </div>
                <p id="payment-status" class="mt-6 inline-flex items-center gap-2 rounded-full bg-amber-50 px-4 py-2 text-xs font-semibold text-amber-700"><span class="size-2 animate-pulse rounded-full bg-amber-500"></span>Menunggu pembayaran...</p>
                @if ($payment->expires_at)<p class="mt-3 text-xs text-[#32170b]/45">Berlaku sampai {{ $payment->expires_at->translatedFormat('d F Y, H.i') }} WIB</p>@endif
            </section>

            <section id="paid-state" class="{{ $payment->status === 'paid' ? '' : 'hidden' }} mt-8 rounded-3xl border border-emerald-200 bg-emerald-50 p-6 text-center">
                <span class="mx-auto grid size-14 place-items-center rounded-full bg-emerald-600 text-xl text-white"><i class="fa-solid fa-check"></i></span>
                <h2 class="mt-4 font-display text-3xl text-emerald-800">Pembayaran Berhasil</h2>
                <p class="mt-2 text-sm text-emerald-800/70">Pembayaran Anda telah berhasil kami terima.</p>
                <p id="invoice-number" class="mt-5 font-semibold text-emerald-900">{{ $payment->invoice?->invoice_number }}</p>
                <a id="invoice-link" href="{{ $payment->invoice ? route('public.invoices.show', $payment->invoice) : '#' }}" class="mt-5 inline-flex rounded-full bg-[#582308] px-6 py-3 text-sm font-semibold text-white">Lihat Invoice</a>
            </section>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const qrContent = {{ Illuminate\Support\Js::from($payment->qr_content) }};
            const canvas = document.getElementById('qris-code');
            if (canvas && qrContent && window.QRCode) {
                window.QRCode.toCanvas(canvas, qrContent, { width: 224, margin: 1, color: { dark: '#32170b', light: '#ffffff' } });
            }

            @if ($payment->status === 'pending')
            const timer = window.setInterval(async () => {
                try {
                    const response = await fetch({{ Illuminate\Support\Js::from(route('public.payments.status', ['payment' => $payment->uuid])) }}, { headers: { Accept: 'application/json' } });
                    if (!response.ok) return;
                    const data = await response.json();
                    if (data.status === 'paid') {
                        window.clearInterval(timer);
                        document.getElementById('pending-state').classList.add('hidden');
                        document.getElementById('paid-state').classList.remove('hidden');
                        document.getElementById('invoice-number').textContent = data.invoice_number || '';
                        document.getElementById('invoice-link').href = data.invoice_url || '#';
                    } else if (['failed', 'expired', 'cancelled'].includes(data.status)) {
                        window.clearInterval(timer);
                        document.getElementById('payment-status').textContent = 'Pembayaran tidak dapat dilanjutkan.';
                    }
                } catch (_) {}
            }, 4000);
            @endif
        });
    </script>
</body>
</html>
