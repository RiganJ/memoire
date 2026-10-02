<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Daftar Pesanan Memoire"><title>Pesanan — Memoire Admin</title>
        @vite('resources/css/app.css')
    </head>
    <body class="bg-[#f4efe7] text-[#32170b] antialiased">
        @php
            $statusLabels = ['waiting' => 'Menunggu data', 'process' => 'Dalam proses', 'revision' => 'Revisi', 'ready' => 'Siap dikirim', 'done' => 'Selesai', 'cancelled' => 'Dibatalkan'];
            $paymentLabels = ['unpaid' => 'Belum lunas', 'paid' => 'Lunas', 'refunded' => 'Dikembalikan'];
        @endphp
        <div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
            @include('admin.partials.sidebar')
            <main class="min-w-0">
                <header class="sticky top-0 z-30 flex h-20 items-center justify-between border-b border-[#582308]/10 bg-[#f4efe7]/90 px-5 backdrop-blur-xl sm:px-8 lg:h-24 lg:px-10">
                    <div><p class="text-xs text-[#32170b]/45">Kelola workspace</p><h1 class="mt-1 font-display text-2xl text-[#582308] sm:text-3xl">Pesanan</h1></div>
                    <a href="{{ route('admin.orders.create') }}" class="flex h-11 items-center gap-2 rounded-full bg-[#582308] px-4 text-xs font-semibold text-white sm:px-5 sm:text-sm"><i class="fa-solid fa-plus text-xs"></i><span class="hidden sm:inline">Pesanan Baru</span></a>
                </header>
                <div class="space-y-6 p-5 sm:p-8 lg:p-10">
                    @if (session('success'))
                        <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800" role="status"><i class="fa-solid fa-circle-check"></i>{{ session('success') }}</div>
                    @endif
                    <section class="grid grid-cols-2 gap-3 xl:grid-cols-4" aria-label="Ringkasan pesanan">
                        @foreach ([[$summary['all'], 'Semua pesanan', 'fa-inbox'], [$summary['process'], 'Dalam proses', 'fa-spinner'], [$summary['waiting'], 'Menunggu tindakan', 'fa-triangle-exclamation'], [$summary['done'], 'Selesai', 'fa-circle-check']] as [$value, $label, $icon])
                            <article class="flex items-center gap-4 rounded-2xl border border-[#582308]/8 bg-white p-4 shadow-[0_8px_25px_rgba(88,35,8,.035)] sm:p-5"><span class="hidden size-11 shrink-0 place-items-center rounded-xl bg-[#ead5ac]/45 text-[#582308] sm:grid"><i class="fa-solid {{ $icon }} text-sm"></i></span><div><p class="font-display text-2xl text-[#582308]">{{ $value }}</p><p class="mt-1 text-[10px] text-[#32170b]/45 sm:text-xs">{{ $label }}</p></div></article>
                        @endforeach
                    </section>
                    <section class="overflow-hidden rounded-3xl border border-[#582308]/8 bg-white shadow-[0_10px_35px_rgba(88,35,8,.04)]">
                        <div class="flex flex-col gap-4 border-b border-[#582308]/8 p-5 sm:p-6 xl:flex-row xl:items-center xl:justify-between">
                            <div><h2 class="font-display text-xl text-[#582308]">Semua pesanan</h2><p class="mt-1 text-xs text-[#32170b]/40">{{ $orders->total() }} pesanan tersimpan</p></div>
                            <form method="GET" class="flex flex-col gap-2 sm:flex-row">
                                <label class="relative block"><span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-[#32170b]/30"><i class="fa-solid fa-magnifying-glass text-xs"></i></span><input name="search" value="{{ request('search') }}" type="search" placeholder="Cari nama atau nomor..." class="h-10 w-full rounded-xl border border-[#582308]/10 bg-[#faf7f0] pl-10 pr-4 text-xs outline-none focus:border-[#bd9150] sm:w-60"></label>
                                <select name="status" class="h-10 rounded-xl border border-[#582308]/10 bg-[#faf7f0] px-3 text-xs text-[#32170b]/65 outline-none focus:border-[#bd9150]"><option value="">Semua status</option>@foreach ($statusLabels as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select>
                                <button class="h-10 rounded-xl border border-[#582308]/10 px-4 text-xs font-semibold text-[#582308]"><i class="fa-solid fa-filter mr-2"></i>Terapkan</button>
                                @if (request()->hasAny(['search', 'status']))<a href="{{ route('admin.orders.index') }}" class="grid h-10 place-items-center px-2 text-xs text-[#32170b]/45">Reset</a>@endif
                            </form>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[980px] text-left">
                                <thead><tr class="border-b border-[#582308]/7 bg-[#faf7f0] text-[9px] uppercase tracking-[.16em] text-[#32170b]/40"><th class="px-6 py-4 font-semibold">Pelanggan</th><th class="px-3 py-4 font-semibold">Detail</th><th class="px-3 py-4 font-semibold">Total</th><th class="px-3 py-4 font-semibold">Tanggal acara</th><th class="px-3 py-4 font-semibold">Pembayaran</th><th class="px-3 py-4 font-semibold">Status</th><th class="px-6 py-4"></th></tr></thead>
                                <tbody class="divide-y divide-[#582308]/7">
                                    @forelse ($orders as $order)
                                        <tr class="text-sm transition hover:bg-[#faf7f0]/75"><td class="px-6 py-4"><div class="flex items-center gap-3"><span class="grid size-9 shrink-0 place-items-center rounded-full bg-[#ead5ac]/55 font-display text-xs font-bold text-[#582308]">{{ collect(explode(' ', $order->customer_name))->map(fn ($word) => mb_substr($word, 0, 1))->take(2)->implode('') }}</span><div><p class="font-semibold text-[#32170b]">{{ $order->customer_name }}</p><p class="mt-1 text-[10px] text-[#32170b]/35">{{ $order->email }}</p></div></div></td><td class="px-3 py-4"><p class="text-xs font-medium text-[#32170b]/70">{{ $order->package }}</p><p class="mt-1 text-[10px] text-[#32170b]/35">{{ $order->event_type }} · {{ $order->order_number }}</p></td><td class="px-3 py-4 text-xs font-semibold text-[#582308]">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</td><td class="px-3 py-4 text-xs text-[#32170b]/50">{{ $order->event_date?->translatedFormat('d M Y') ?? 'Belum ditentukan' }}</td><td class="px-3 py-4"><span class="text-[10px] font-semibold {{ $order->payment_status === 'paid' ? 'text-emerald-700' : ($order->payment_status === 'unpaid' ? 'text-amber-700' : 'text-[#32170b]/45') }}">{{ $paymentLabels[$order->payment_status] }}</span></td><td class="px-3 py-4"><span class="admin-status admin-status-{{ $order->status }}"><span></span>{{ $statusLabels[$order->status] }}</span></td><td class="px-6 py-4"><div class="flex justify-end gap-1"><a href="{{ route('admin.orders.show', $order) }}" class="grid size-8 place-items-center rounded-lg text-[#32170b]/35 hover:bg-[#ead5ac]/40 hover:text-[#582308]" aria-label="Lihat pesanan"><i class="fa-regular fa-eye"></i></a><a href="{{ route('admin.orders.edit', $order) }}" class="grid size-8 place-items-center rounded-lg text-[#32170b]/35 hover:bg-[#ead5ac]/40 hover:text-[#582308]" aria-label="Edit pesanan"><i class="fa-solid fa-pen"></i></a><form action="{{ route('admin.orders.destroy', $order) }}" method="POST" onsubmit="return confirm('Hapus pesanan {{ $order->order_number }}?')">@csrf @method('DELETE')<button class="grid size-8 place-items-center rounded-lg text-[#32170b]/35 hover:bg-red-50 hover:text-red-700" aria-label="Hapus pesanan"><i class="fa-regular fa-trash-can"></i></button></form></div></td></tr>
                                    @empty
                                        <tr><td colspan="7" class="px-6 py-16 text-center"><span class="mx-auto grid size-14 place-items-center rounded-full bg-[#ead5ac]/35 text-[#582308]"><i class="fa-solid fa-inbox"></i></span><p class="mt-4 font-display text-lg text-[#582308]">Belum ada pesanan</p><p class="mt-1 text-xs text-[#32170b]/40">Tambahkan pesanan pertama atau ubah filter pencarian.</p></td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($orders->hasPages())<div class="border-t border-[#582308]/8 px-6 py-4">{{ $orders->links() }}</div>@endif
                    </section>
                </div>
            </main>
        </div>
    </body>
</html>
