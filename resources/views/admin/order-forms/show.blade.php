<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $orderForm->name }} — Memoire Admin</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-[#f4efe7] text-[#32170b] antialiased">
<div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
    @include('admin.partials.sidebar')
    <main class="min-w-0">
        <header class="border-b border-[#582308]/10 px-5 py-6 sm:px-8 lg:px-10">
            <a href="{{ route('admin.order-forms.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-[#582308]/60 transition hover:text-[#582308]"><i class="fa-solid fa-arrow-left"></i>Semua form</a>
            <div class="mt-4 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[9px] font-bold uppercase tracking-[.22em] text-[#582308]/45">Detail formulir</p>
                    <h1 class="mt-2 font-display text-3xl text-[#582308] sm:text-4xl">{{ $orderForm->name }}</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-[#32170b]/50">{{ $orderForm->description ?: 'Belum ada deskripsi untuk form ini.' }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if($orderForm->is_active)
                        <span class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-emerald-50 px-4 text-xs font-bold text-emerald-700"><span class="size-1.5 rounded-full bg-emerald-500"></span>Aktif di checkout</span>
                    @else
                        <form action="{{ route('admin.order-forms.activate', $orderForm) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-[#ead5ac] px-4 text-xs font-bold text-[#582308] transition hover:bg-[#f0dfbd]"><i class="fa-solid fa-bolt"></i>Jadikan aktif</button>
                        </form>
                    @endif
                    <a href="{{ route('admin.order-forms.edit', $orderForm) }}" class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-[#582308] px-4 text-xs font-bold text-white transition hover:bg-[#713719]"><i class="fa-solid fa-pen"></i>Edit form</a>
                </div>
            </div>
        </header>

        <div class="mx-auto max-w-5xl space-y-6 p-5 sm:p-8 lg:p-10">
            @if(session('success'))
                <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800"><i class="fa-solid fa-circle-check"></i>{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800"><i class="fa-solid fa-circle-exclamation"></i>{{ session('error') }}</div>
            @endif

            <div class="grid gap-4 sm:grid-cols-3">
                <article class="rounded-2xl border border-[#582308]/10 bg-white p-5 shadow-sm"><p class="text-[9px] font-bold uppercase tracking-[.18em] text-[#32170b]/40">Status</p><p class="mt-3 text-sm font-bold {{ $orderForm->is_active ? 'text-emerald-700' : 'text-stone-600' }}">{{ $orderForm->is_active ? 'Sedang digunakan' : 'Nonaktif' }}</p></article>
                <article class="rounded-2xl border border-[#582308]/10 bg-white p-5 shadow-sm"><p class="text-[9px] font-bold uppercase tracking-[.18em] text-[#32170b]/40">Jumlah pertanyaan</p><p class="mt-3 font-display text-2xl text-[#582308]">{{ count($orderForm->fields) }}</p></article>
                <article class="rounded-2xl border border-[#582308]/10 bg-white p-5 shadow-sm"><p class="text-[9px] font-bold uppercase tracking-[.18em] text-[#32170b]/40">Terakhir diperbarui</p><p class="mt-3 text-sm font-bold text-[#582308]">{{ $orderForm->updated_at->translatedFormat('d F Y') }}</p></article>
            </div>

            <section class="rounded-3xl border border-[#582308]/10 bg-white p-5 shadow-[0_8px_28px_rgba(88,35,8,.04)] sm:p-7">
                <div class="flex items-center justify-between gap-4 border-b border-[#582308]/8 pb-5">
                    <div><p class="text-[9px] font-bold uppercase tracking-[.2em] text-[#582308]/45">Pratinjau</p><h2 class="mt-1 font-display text-2xl text-[#582308]">Pertanyaan pelanggan</h2></div>
                    <span class="grid size-11 place-items-center rounded-2xl bg-[#f4e8d3] text-[#582308]"><i class="fa-solid fa-list-check"></i></span>
                </div>
                <ol class="mt-5 space-y-3">
                    @foreach($orderForm->fields as $index => $field)
                        <li class="flex items-start gap-4 rounded-2xl border border-[#582308]/8 bg-[#fffdfa] p-4 sm:p-5">
                            <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-[#f4e8d3] text-[10px] font-bold text-[#582308]">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-bold text-[#582308]">{{ $field['label'] }}</h3>
                                    @if($field['required'])
                                        <span class="rounded-full bg-rose-50 px-2 py-1 text-[9px] font-bold text-rose-600">Wajib</span>
                                    @else
                                        <span class="rounded-full bg-stone-100 px-2 py-1 text-[9px] font-semibold text-stone-500">Opsional</span>
                                    @endif
                                </div>
                                <p class="mt-1.5 text-[10px] font-medium text-[#32170b]/40">{{ ['text' => 'Jawaban singkat', 'textarea' => 'Jawaban panjang', 'date' => 'Tanggal', 'select' => 'Pilihan'][$field['type']] ?? 'Jawaban' }}</p>
                                @if($field['type'] === 'select')
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @foreach($field['options'] as $option)
                                            <span class="rounded-full border border-[#582308]/10 px-2.5 py-1 text-[10px] text-[#32170b]/55">{{ $option }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>

            @unless($orderForm->is_active)
                <section class="flex flex-col gap-4 rounded-3xl border border-red-100 bg-white p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                    <div><h2 class="font-display text-lg text-[#582308]">Hapus form ini</h2><p class="mt-1 text-xs leading-5 text-[#32170b]/45">Form nonaktif dapat dihapus permanen. Form aktif harus diganti terlebih dahulu.</p></div>
                    <form action="{{ route('admin.order-forms.destroy', $orderForm) }}" method="POST" onsubmit="return confirm('Hapus form {{ $orderForm->name }}? Tindakan ini tidak dapat dibatalkan.')">
                        @csrf
                        @method('DELETE')
                        <button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-red-200 px-4 text-xs font-bold text-red-700 transition hover:bg-red-50"><i class="fa-regular fa-trash-can"></i>Hapus form</button>
                    </form>
                </section>
            @endunless
        </div>
    </main>
</div>
</body>
</html>
