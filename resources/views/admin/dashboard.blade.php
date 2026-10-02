<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Dashboard Admin Memoire">
        <title>Dashboard Admin — Memoire</title>
        @vite('resources/css/app.css')
    </head>
    <body class="bg-[#f4efe7] text-[#32170b] antialiased">
        @php
            $orders = [
                ['INV-0241', 'Nadia & Arka', 'Signature', 'Pernikahan', '27 Sep 2026', 'Dalam proses', 'process'],
                ['INV-0240', 'Aluna Prameswari', 'Essential', 'Ulang tahun', '26 Sep 2026', 'Menunggu data', 'waiting'],
                ['INV-0239', 'PT Nusa Kreatif', 'Bespoke', 'Corporate event', '25 Sep 2026', 'Siap dikirim', 'ready'],
                ['INV-0238', 'Raisa & Damar', 'Signature', 'Engagement', '24 Sep 2026', 'Selesai', 'done'],
                ['INV-0237', 'Keluarga Mahendra', 'Essential', 'Syukuran', '23 Sep 2026', 'Selesai', 'done'],
            ];
        @endphp

        <div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
            @include('admin.partials.sidebar')

            <main class="min-w-0">
                <header class="sticky top-0 z-30 flex h-20 items-center justify-between border-b border-[#582308]/10 bg-[#f4efe7]/90 px-5 backdrop-blur-xl sm:px-8 lg:h-24 lg:px-10">
                    <div><p class="text-xs text-[#32170b]/45">Minggu, 27 September 2026</p><h1 class="mt-1 font-display text-2xl text-[#582308] sm:text-3xl">Selamat datang kembali.</h1></div>
                    <div class="flex items-center gap-2">
                        <button class="relative grid size-11 place-items-center rounded-full border border-[#582308]/10 bg-white text-[#582308]" aria-label="Notifikasi"><i class="fa-regular fa-bell"></i><span class="absolute right-0 top-0 size-2.5 rounded-full border-2 border-[#f4efe7] bg-[#bd9150]"></span></button>
                        <button class="hidden h-11 items-center gap-2 rounded-full bg-[#582308] px-5 text-sm font-semibold text-white sm:flex"><i class="fa-solid fa-plus text-xs"></i>Pesanan Baru</button>
                    </div>
                </header>

                <div class="space-y-8 p-5 sm:p-8 lg:p-10">
                    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan">
                        @foreach ([['24', 'Pesanan aktif', '+8.2%', 'fa-envelope-open-text'], ['8', 'Menunggu data', 'Perlu tindakan', 'fa-clock'], ['12', 'Selesai bulan ini', '+3 dari Agustus', 'fa-circle-check'], ['Rp 8,4 jt', 'Pendapatan bulan ini', '+12.4%', 'fa-chart-line']] as $index => [$value, $label, $note, $icon])
                            <article class="group relative overflow-hidden rounded-2xl border border-[#582308]/8 bg-white p-5 shadow-[0_8px_30px_rgba(88,35,8,.04)] transition hover:-translate-y-1 hover:shadow-[0_14px_35px_rgba(88,35,8,.08)]">
                                <span class="absolute -right-4 -top-5 font-display text-7xl text-[#582308]/[.025]">0{{ $index + 1 }}</span>
                                <div class="flex items-start justify-between"><span class="grid size-10 place-items-center rounded-xl bg-[#ead5ac]/55 text-[#582308]"><i class="fa-solid {{ $icon }} text-sm"></i></span><span class="rounded-full bg-[#faf7f0] px-2.5 py-1 text-[9px] font-semibold text-[#582308]/55">{{ $note }}</span></div>
                                <p class="mt-6 font-display text-3xl text-[#582308]">{{ $value }}</p><p class="mt-1 text-xs text-[#32170b]/50">{{ $label }}</p>
                            </article>
                        @endforeach
                    </section>

                    <div class="grid gap-6 xl:grid-cols-[1.45fr_.55fr]">
                        <section class="overflow-hidden rounded-3xl border border-[#582308]/8 bg-white shadow-[0_10px_35px_rgba(88,35,8,.04)]">
                            <div class="flex items-center justify-between border-b border-[#582308]/8 px-6 py-5"><div><h2 class="font-display text-xl text-[#582308]">Pesanan terbaru</h2><p class="mt-1 text-xs text-[#32170b]/40">Kelola dan pantau progres pesanan</p></div><a href="{{ route('admin.orders.index') }}" class="text-xs font-semibold text-[#582308]">Lihat semua <span class="ml-1">→</span></a></div>
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[780px] text-left">
                                    <thead><tr class="border-b border-[#582308]/7 bg-[#faf7f0] text-[9px] uppercase tracking-[.16em] text-[#32170b]/40"><th class="px-6 py-4 font-semibold">Pesanan</th><th class="px-4 py-4 font-semibold">Paket</th><th class="px-4 py-4 font-semibold">Jenis</th><th class="px-4 py-4 font-semibold">Tanggal</th><th class="px-4 py-4 font-semibold">Status</th><th class="px-6 py-4"></th></tr></thead>
                                    <tbody class="divide-y divide-[#582308]/7">
                                        @foreach ($orders as [$number, $customer, $package, $type, $date, $status, $statusClass])
                                            <tr class="text-sm transition hover:bg-[#faf7f0]/75"><td class="px-6 py-4"><p class="font-semibold text-[#32170b]">{{ $customer }}</p><p class="mt-1 text-[10px] text-[#32170b]/35">{{ $number }}</p></td><td class="px-4 py-4 text-xs text-[#32170b]/60">{{ $package }}</td><td class="px-4 py-4 text-xs text-[#32170b]/60">{{ $type }}</td><td class="px-4 py-4 text-xs text-[#32170b]/50">{{ $date }}</td><td class="px-4 py-4"><span class="admin-status admin-status-{{ $statusClass }}"><span></span>{{ $status }}</span></td><td class="px-6 py-4 text-right"><button class="grid size-8 place-items-center rounded-lg text-[#32170b]/40 hover:bg-[#ead5ac]/40 hover:text-[#582308]" aria-label="Menu pesanan"><i class="fa-solid fa-ellipsis"></i></button></td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <div class="space-y-6">
                            <section class="rounded-3xl bg-[#582308] p-6 text-white shadow-[0_20px_50px_rgba(88,35,8,.16)]">
                                <div class="flex items-center justify-between"><div><p class="text-[9px] font-bold uppercase tracking-[.22em] text-[#ead5ac]">Target Bulanan</p><h2 class="mt-2 font-display text-2xl">September</h2></div><span class="grid size-11 place-items-center rounded-full border border-white/15 text-[#ead5ac]"><i class="fa-solid fa-bullseye"></i></span></div>
                                <div class="mt-8 flex items-end justify-between"><p class="font-display text-4xl">72%</p><p class="text-xs text-white/45">18 dari 25 pesanan</p></div><div class="mt-4 h-2 overflow-hidden rounded-full bg-white/10"><div class="h-full w-[72%] rounded-full bg-[#ead5ac]"></div></div><p class="mt-5 text-xs leading-6 text-white/45">Tersisa 7 pesanan lagi untuk mencapai target bulan ini.</p>
                            </section>

                            <section class="rounded-3xl border border-[#582308]/8 bg-white p-6 shadow-[0_10px_35px_rgba(88,35,8,.04)]">
                                <div class="flex items-center justify-between"><h2 class="font-display text-xl text-[#582308]">Aktivitas terbaru</h2><button class="text-[#32170b]/35"><i class="fa-solid fa-ellipsis"></i></button></div>
                                <div class="mt-6 space-y-5">
                                    @foreach ([['fa-check', 'Pesanan INV-0238 selesai', '12 menit lalu'], ['fa-comment', 'Ucapan baru dari Nadia', '36 menit lalu'], ['fa-credit-card', 'Pembayaran telah diterima', '1 jam lalu'], ['fa-image', 'Galeri Aluna diperbarui', '3 jam lalu']] as [$icon, $activity, $time])
                                        <div class="flex gap-3"><span class="grid size-8 shrink-0 place-items-center rounded-full bg-[#ead5ac]/45 text-[10px] text-[#582308]"><i class="fa-solid {{ $icon }}"></i></span><div><p class="text-xs font-medium text-[#32170b]/75">{{ $activity }}</p><p class="mt-1 text-[10px] text-[#32170b]/35">{{ $time }}</p></div></div>
                                    @endforeach
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>
