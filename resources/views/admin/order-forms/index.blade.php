<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Form Pesanan — Memoire Admin</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-[#f4efe7] text-[#32170b] antialiased">
<div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
    @include('admin.partials.sidebar')
    <main class="min-w-0">
        <header class="border-b border-[#582308]/10 px-5 py-7 sm:px-8 lg:px-10">
            <p class="text-[10px] font-bold uppercase tracking-[.22em] text-[#582308]/45">Studio formulir</p>
            <div class="mt-2 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="font-display text-3xl text-[#582308] sm:text-4xl">Form Pesanan</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-[#32170b]/55">Rancang beberapa pengalaman pemesanan. Satu form aktif akan digunakan oleh seluruh katalog.</p>
                </div>
                <a href="{{ route('admin.order-forms.create') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#582308] px-5 text-sm font-bold text-white shadow-[0_10px_24px_rgba(88,35,8,.14)] transition hover:-translate-y-0.5 hover:bg-[#713719]">
                    <i class="fa-solid fa-plus text-xs"></i> Buat form baru
                </a>
            </div>
        </header>

        <div class="space-y-7 p-5 sm:p-8 lg:p-10">
            @if(session('success'))
                <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800"><i class="fa-solid fa-circle-check"></i>{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800"><i class="fa-solid fa-circle-exclamation"></i>{{ session('error') }}</div>
            @endif

            <section class="relative overflow-hidden rounded-[2rem] bg-[#582308] p-6 text-white shadow-[0_20px_55px_rgba(88,35,8,.16)] sm:p-9">
                <div class="pointer-events-none absolute -right-16 -top-28 size-80 rounded-full border border-[#ead5ac]/15"></div>
                <div class="pointer-events-none absolute -right-2 -top-14 size-52 rounded-full border border-[#ead5ac]/15"></div>
                <div class="relative grid gap-8 lg:grid-cols-[1fr_auto] lg:items-center">
                    <div>
                        <p class="text-[9px] font-bold uppercase tracking-[.28em] text-[#ead5ac]/70">Sedang digunakan di checkout</p>
                        @if($activeForm)
                            <h2 class="mt-3 font-display text-3xl text-[#ead5ac] sm:text-4xl">{{ $activeForm->name }}</h2>
                            <p class="mt-3 max-w-xl text-sm leading-6 text-white/65">{{ $activeForm->description ?: 'Form ini menjadi alur pemesanan utama untuk semua katalog.' }}</p>
                            <div class="mt-5 flex flex-wrap items-center gap-3 text-xs text-white/60">
                                <span class="inline-flex items-center gap-2 rounded-full border border-white/15 px-3 py-1.5"><span class="size-1.5 rounded-full bg-emerald-400"></span>Aktif</span>
                                <span>{{ count($activeForm->fields) }} pertanyaan</span>
                            </div>
                        @else
                            <h2 class="mt-3 font-display text-3xl text-[#ead5ac]">Belum ada form aktif</h2>
                            <p class="mt-3 text-sm leading-6 text-white/65">Pilih satu form di bawah agar pelanggan dapat melanjutkan checkout.</p>
                        @endif
                    </div>
                    @if($activeForm)
                        <a href="{{ route('admin.order-forms.show', $activeForm) }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#ead5ac] px-5 text-sm font-bold text-[#582308] transition hover:bg-[#f3e4c3]">
                            Lihat form <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    @endif
                </div>
            </section>

            <section>
                <div class="mb-4 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-[9px] font-bold uppercase tracking-[.2em] text-[#582308]/45">Pustaka formulir</p>
                        <h2 class="mt-1 font-display text-2xl text-[#582308]">Semua form <span class="font-sans text-sm font-medium text-[#32170b]/40">({{ $forms->count() }})</span></h2>
                    </div>
                    <p class="hidden text-xs text-[#32170b]/45 sm:block"><i class="fa-solid fa-circle-info mr-1"></i>Hanya satu form dapat aktif</p>
                </div>

                @if($forms->isNotEmpty())
                    <div class="grid gap-4 xl:grid-cols-2">
                        @foreach($forms as $form)
                            <article class="rounded-3xl border {{ $form->is_active ? 'border-[#bd9150]/50 bg-[#fffdfa] shadow-[0_12px_34px_rgba(88,35,8,.08)]' : 'border-[#582308]/10 bg-white/75 shadow-sm' }} p-5 transition hover:-translate-y-0.5 sm:p-6">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex min-w-0 items-start gap-4">
                                        <span class="grid size-12 shrink-0 place-items-center rounded-2xl {{ $form->is_active ? 'bg-[#582308] text-[#ead5ac]' : 'bg-[#f4e8d3] text-[#582308]' }}"><i class="fa-regular fa-rectangle-list"></i></span>
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h3 class="font-display text-xl text-[#582308]">{{ $form->name }}</h3>
                                                @if($form->is_active)
                                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wide text-emerald-700">Aktif</span>
                                                @else
                                                    <span class="rounded-full bg-stone-100 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wide text-stone-600">Nonaktif</span>
                                                @endif
                                            </div>
                                            <p class="mt-2 line-clamp-2 text-xs leading-5 text-[#32170b]/50">{{ $form->description ?: 'Belum ada deskripsi form.' }}</p>
                                            <p class="mt-3 text-[10px] font-semibold text-[#32170b]/40"><i class="fa-solid fa-list-check mr-1.5"></i>{{ count($form->fields) }} pertanyaan <span class="mx-1">·</span> Diperbarui {{ $form->updated_at->translatedFormat('d M Y') }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-5 flex flex-wrap gap-2 border-t border-[#582308]/8 pt-4">
                                    <a href="{{ route('admin.order-forms.show', $form) }}" class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-[#582308]/12 px-3.5 text-xs font-bold text-[#582308] transition hover:bg-[#faf7f0]"><i class="fa-regular fa-eye"></i>Lihat</a>
                                    <a href="{{ route('admin.order-forms.edit', $form) }}" class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-[#582308]/12 px-3.5 text-xs font-bold text-[#582308] transition hover:bg-[#faf7f0]"><i class="fa-solid fa-pen"></i>Edit</a>
                                    @if(!$form->is_active)
                                        <form action="{{ route('admin.order-forms.activate', $form) }}" method="POST" class="ml-auto">
                                            @csrf
                                            @method('PATCH')
                                            <button class="inline-flex min-h-10 items-center gap-2 rounded-xl bg-[#ead5ac] px-3.5 text-xs font-bold text-[#582308] transition hover:bg-[#f0dfbd]"><i class="fa-solid fa-bolt"></i>Jadikan aktif</button>
                                        </form>
                                        <form action="{{ route('admin.order-forms.destroy', $form) }}" method="POST" onsubmit="return confirm('Hapus form {{ $form->name }}? Tindakan ini tidak dapat dibatalkan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button aria-label="Hapus {{ $form->name }}" class="grid size-10 place-items-center rounded-xl text-red-600 transition hover:bg-red-50"><i class="fa-regular fa-trash-can"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-3xl border border-dashed border-[#582308]/20 bg-white/60 px-6 py-14 text-center">
                        <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-[#f4e8d3] text-xl text-[#582308]"><i class="fa-regular fa-rectangle-list"></i></span>
                        <h3 class="mt-4 font-display text-xl text-[#582308]">Belum ada form tambahan</h3>
                        <p class="mt-2 text-sm text-[#32170b]/50">Buat form baru untuk menyiapkan alur checkout yang berbeda.</p>
                        <a href="{{ route('admin.order-forms.create') }}" class="mt-5 inline-flex min-h-11 items-center gap-2 rounded-xl bg-[#582308] px-5 text-sm font-bold text-white"><i class="fa-solid fa-plus text-xs"></i>Buat form pertama</a>
                    </div>
                @endif
            </section>
        </div>
    </main>
</div>
</body>
</html>
