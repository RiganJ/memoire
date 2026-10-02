<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Pembayaran Memoire">
        <title>Pembayaran — Memoire Admin</title>
        @vite('resources/css/app.css')
    </head>
    <body class="bg-[#f4efe7] text-[#32170b] antialiased">
        @php
            $payments = [
                ['PAY-0927', 'INV-0241', 'Nadia & Arka', 'Transfer Bank', 'Rp 179.000', '27 Sep 2026 · 14.32', 'Berhasil', 'success'],
                ['PAY-0926', 'INV-0240', 'Aluna Prameswari', 'QRIS', 'Rp 99.000', '26 Sep 2026 · 19.08', 'Berhasil', 'success'],
                ['PAY-0925', 'INV-0239', 'PT Nusa Kreatif', 'Transfer Bank', 'Rp 399.000', '25 Sep 2026 · 10.14', 'Berhasil', 'success'],
                ['PAY-0924', 'INV-0238', 'Raisa & Damar', 'E-Wallet', 'Rp 179.000', '24 Sep 2026 · 20.45', 'Berhasil', 'success'],
                ['PAY-0923', 'INV-0236', 'Dinda Larasati', 'QRIS', 'Rp 179.000', '22 Sep 2026 · 09.21', 'Menunggu', 'pending'],
                ['PAY-0922', 'INV-0235', 'Kirana & Raka', 'Transfer Bank', 'Rp 399.000', '21 Sep 2026 · 16.55', 'Berhasil', 'success'],
                ['PAY-0921', 'INV-0234', 'Studio Senandika', 'E-Wallet', 'Rp 99.000', '20 Sep 2026 · 11.03', 'Dikembalikan', 'refund'],
            ];
        @endphp

        <div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
            @include('admin.partials.sidebar')

            <main class="min-w-0">
                <header class="sticky top-0 z-30 flex h-20 items-center justify-between border-b border-[#582308]/10 bg-[#f4efe7]/90 px-5 backdrop-blur-xl sm:px-8 lg:h-24 lg:px-10">
                    <div><p class="text-xs text-[#32170b]/45">Kelola transaksi</p><h1 class="mt-1 font-display text-2xl text-[#582308] sm:text-3xl">Pembayaran</h1></div>
                    <button class="flex h-11 items-center gap-2 rounded-full border border-[#582308]/12 bg-white px-4 text-xs font-semibold text-[#582308] sm:px-5 sm:text-sm"><i class="fa-solid fa-file-export text-xs"></i><span class="hidden sm:inline">Ekspor Laporan</span></button>
                </header>

                <div class="space-y-6 p-5 sm:p-8 lg:p-10">
                    <section class="grid grid-cols-2 gap-3 xl:grid-cols-4" aria-label="Ringkasan pembayaran">
                        @foreach ([['Rp 8,4 jt', 'Pendapatan bulan ini', '+12,4%', 'fa-chart-line'], ['Rp 1,45 jt', 'Dana minggu ini', '+4 transaksi', 'fa-wallet'], ['1', 'Menunggu pembayaran', 'Perlu ditinjau', 'fa-clock'], ['Rp 99 rb', 'Dana dikembalikan', '1 transaksi', 'fa-rotate-left']] as [$value, $label, $note, $icon])
                            <article class="rounded-2xl border border-[#582308]/8 bg-white p-4 shadow-[0_8px_25px_rgba(88,35,8,.035)] sm:p-5">
                                <div class="flex items-start justify-between gap-3"><span class="grid size-10 place-items-center rounded-xl bg-[#ead5ac]/45 text-[#582308]"><i class="fa-solid {{ $icon }} text-sm"></i></span><span class="text-right text-[9px] font-semibold text-[#32170b]/35">{{ $note }}</span></div>
                                <p class="mt-5 font-display text-2xl text-[#582308] sm:text-3xl">{{ $value }}</p><p class="mt-1 text-[10px] text-[#32170b]/45 sm:text-xs">{{ $label }}</p>
                            </article>
                        @endforeach
                    </section>

                    <div class="grid gap-6 xl:grid-cols-[1.7fr_.8fr]">
                        <section class="rounded-3xl border border-[#582308]/8 bg-white p-5 shadow-[0_10px_35px_rgba(88,35,8,.04)] sm:p-6">
                            <div class="flex items-start justify-between gap-4"><div><h2 class="font-display text-xl text-[#582308]">Arus pendapatan</h2><p class="mt-1 text-xs text-[#32170b]/40">Performa transaksi enam bulan terakhir</p></div><select class="rounded-xl border border-[#582308]/10 bg-[#faf7f0] px-3 py-2 text-[10px] text-[#32170b]/60 outline-none"><option>6 bulan</option><option>12 bulan</option></select></div>
                            <div class="mt-8 flex h-48 items-end gap-3 border-b border-[#582308]/10 sm:gap-5" aria-label="Grafik pendapatan bulanan">
                                @foreach ([['Apr', 44, '3,1 jt'], ['Mei', 57, '4,2 jt'], ['Jun', 48, '3,6 jt'], ['Jul', 72, '5,9 jt'], ['Agu', 64, '5,1 jt'], ['Sep', 92, '8,4 jt']] as [$month, $height, $amount])
                                    <div class="group flex h-full flex-1 flex-col justify-end gap-2 text-center"><span class="text-[9px] font-semibold text-[#582308] opacity-0 transition group-hover:opacity-100">{{ $amount }}</span><div class="mx-auto w-full max-w-12 rounded-t-xl {{ $month === 'Sep' ? 'bg-[#582308]' : 'bg-[#ead5ac]' }}" style="height: {{ $height }}%"></div><span class="pb-3 text-[9px] text-[#32170b]/40">{{ $month }}</span></div>
                                @endforeach
                            </div>
                        </section>

                        <section class="relative overflow-hidden rounded-3xl bg-[#582308] p-6 text-white shadow-[0_20px_50px_rgba(88,35,8,.16)]">
                            <div class="absolute -right-12 -top-12 size-40 rounded-full border border-[#ead5ac]/15"></div><div class="absolute -right-5 -top-5 size-24 rounded-full border border-[#ead5ac]/15"></div>
                            <span class="grid size-11 place-items-center rounded-xl bg-[#ead5ac] text-[#582308]"><i class="fa-solid fa-building-columns"></i></span>
                            <p class="mt-8 text-[9px] font-bold uppercase tracking-[.22em] text-[#ead5ac]/70">Saldo tersedia</p><p class="mt-2 font-display text-3xl text-[#ead5ac]">Rp 7.842.000</p><p class="mt-2 text-xs leading-6 text-white/45">Diperbarui hari ini pukul 15.30 WIB.</p>
                            <div class="mt-7 border-t border-white/10 pt-5"><div class="flex items-center justify-between text-xs"><span class="text-white/45">Rekening utama</span><span class="font-semibold">•••• 8826</span></div><button class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl bg-[#ead5ac] py-3 text-xs font-semibold text-[#582308]"><i class="fa-solid fa-arrow-right-arrow-left"></i>Kelola pencairan</button></div>
                        </section>
                    </div>

                    <section class="overflow-hidden rounded-3xl border border-[#582308]/8 bg-white shadow-[0_10px_35px_rgba(88,35,8,.04)]">
                        <div class="flex flex-col gap-4 border-b border-[#582308]/8 p-5 sm:p-6 xl:flex-row xl:items-center xl:justify-between">
                            <div><h2 class="font-display text-xl text-[#582308]">Riwayat transaksi</h2><p class="mt-1 text-xs text-[#32170b]/40">Pantau seluruh pembayaran pesanan</p></div>
                            <div class="flex flex-col gap-2 sm:flex-row"><label class="relative block"><span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-[#32170b]/30"><i class="fa-solid fa-magnifying-glass text-xs"></i></span><input type="search" placeholder="Cari transaksi..." class="h-10 w-full rounded-xl border border-[#582308]/10 bg-[#faf7f0] pl-10 pr-4 text-xs outline-none focus:border-[#bd9150] sm:w-56"></label><select class="h-10 rounded-xl border border-[#582308]/10 bg-[#faf7f0] px-3 text-xs text-[#32170b]/65 outline-none"><option>Semua status</option><option>Berhasil</option><option>Menunggu</option><option>Dikembalikan</option></select></div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[900px] text-left">
                                <thead><tr class="border-b border-[#582308]/7 bg-[#faf7f0] text-[9px] uppercase tracking-[.16em] text-[#32170b]/40"><th class="px-6 py-4 font-semibold">Transaksi</th><th class="px-3 py-4 font-semibold">Pelanggan</th><th class="px-3 py-4 font-semibold">Metode</th><th class="px-3 py-4 font-semibold">Tanggal</th><th class="px-3 py-4 font-semibold">Nominal</th><th class="px-3 py-4 font-semibold">Status</th><th class="px-6 py-4"></th></tr></thead>
                                <tbody class="divide-y divide-[#582308]/7">
                                    @foreach ($payments as [$number, $order, $customer, $method, $amount, $date, $status, $statusClass])
                                        <tr class="text-sm transition hover:bg-[#faf7f0]/75"><td class="px-6 py-4"><p class="text-xs font-semibold text-[#582308]">{{ $number }}</p><p class="mt-1 text-[10px] text-[#32170b]/35">{{ $order }}</p></td><td class="px-3 py-4"><div class="flex items-center gap-3"><span class="grid size-9 shrink-0 place-items-center rounded-full bg-[#ead5ac]/55 font-display text-xs font-bold text-[#582308]">{{ collect(explode(' ', $customer))->map(fn ($word) => mb_substr($word, 0, 1))->take(2)->implode('') }}</span><span class="text-xs font-semibold text-[#32170b]">{{ $customer }}</span></div></td><td class="px-3 py-4 text-xs text-[#32170b]/55"><i class="fa-solid {{ $method === 'QRIS' ? 'fa-qrcode' : ($method === 'E-Wallet' ? 'fa-wallet' : 'fa-building-columns') }} mr-2 text-[#bd9150]"></i>{{ $method }}</td><td class="px-3 py-4 text-xs text-[#32170b]/45">{{ $date }}</td><td class="px-3 py-4 text-xs font-semibold text-[#582308]">{{ $amount }}</td><td class="px-3 py-4"><span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[10px] font-semibold {{ $statusClass === 'success' ? 'bg-emerald-50 text-emerald-700' : ($statusClass === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-stone-100 text-stone-600') }}"><span class="size-1.5 rounded-full bg-current"></span>{{ $status }}</span></td><td class="px-6 py-4"><button class="grid size-8 place-items-center rounded-lg text-[#32170b]/35 transition hover:bg-[#ead5ac]/40 hover:text-[#582308]" aria-label="Lihat detail {{ $number }}"><i class="fa-solid fa-ellipsis"></i></button></td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="flex flex-col items-center justify-between gap-4 border-t border-[#582308]/8 px-6 py-4 sm:flex-row"><p class="text-[10px] text-[#32170b]/40">Menampilkan 7 transaksi terbaru</p><div class="flex items-center gap-1"><button class="grid size-8 place-items-center rounded-lg border border-[#582308]/10 text-xs text-[#32170b]/35"><i class="fa-solid fa-chevron-left"></i></button><button class="grid size-8 place-items-center rounded-lg bg-[#582308] text-xs text-white">1</button><button class="grid size-8 place-items-center rounded-lg text-xs text-[#32170b]/55 hover:bg-[#ead5ac]/35">2</button><button class="grid size-8 place-items-center rounded-lg border border-[#582308]/10 text-xs text-[#582308]"><i class="fa-solid fa-chevron-right"></i></button></div></div>
                    </section>
                </div>
            </main>
        </div>
    </body>
</html>
