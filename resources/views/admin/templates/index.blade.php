<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Kelola template undangan digital Memoire">
        <title>Template Undangan — Memoire Admin</title>
        @vite('resources/css/app.css')
    </head>
    <body class="bg-[#f4efe7] text-[#32170b] antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
            @include('admin.partials.sidebar')
            <main class="min-w-0">
                <header class="sticky top-0 z-30 flex h-20 items-center justify-between gap-4 border-b border-[#582308]/10 bg-[#f4efe7]/90 px-5 backdrop-blur-xl sm:px-8 lg:h-24 lg:px-10">
                    <div><p class="text-xs text-[#32170b]/45">Website undangan</p><h1 class="mt-1 font-display text-2xl text-[#582308] sm:text-3xl">Template Undangan</h1></div>
                    <a href="{{ route('admin.templates.create') }}" class="flex h-11 shrink-0 items-center gap-2 rounded-full bg-[#582308] px-4 text-xs font-semibold text-white shadow-[0_10px_25px_rgba(88,35,8,.15)] transition hover:-translate-y-0.5 hover:bg-[#713719] sm:px-5 sm:text-sm"><i class="fa-solid fa-plus text-xs"></i><span class="hidden sm:inline">Template Baru</span></a>
                </header>

                <div class="space-y-6 p-5 sm:p-8 lg:p-10">
                    @if (session('success'))<div role="status" class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800"><i class="fa-solid fa-circle-check"></i>{{ session('success') }}</div>@endif
                    @if ($errors->any())<div role="alert" class="flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700"><i class="fa-solid fa-circle-exclamation"></i>{{ $errors->first() }}</div>@endif

                    <section class="overflow-hidden rounded-3xl bg-[#582308] text-white shadow-[0_18px_45px_rgba(88,35,8,.14)]">
                        <div class="relative grid gap-6 p-6 sm:p-8 xl:grid-cols-[1fr_auto] xl:items-end">
                            <div class="pointer-events-none absolute -right-12 -top-20 size-64 rounded-full border border-[#ead5ac]/15"></div>
                            <div class="pointer-events-none absolute -right-4 -top-12 size-40 rounded-full border border-[#ead5ac]/10"></div>
                            <div class="relative max-w-xl"><span class="grid size-11 place-items-center rounded-2xl bg-[#ead5ac]/15 text-[#ead5ac]"><i class="fa-regular fa-envelope-open"></i></span><h2 class="mt-5 font-display text-2xl text-[#ead5ac] sm:text-3xl">Kelola setiap cerita dari satu tempat.</h2><p class="mt-2 text-xs leading-6 text-white/55 sm:text-sm">Unggah template, cek tampilannya, lalu publikasikan saat semua detail sudah siap.</p></div>
                            <div class="relative flex items-center gap-3 rounded-2xl border border-white/10 bg-white/[.06] p-4 backdrop-blur"><p class="font-display text-3xl text-[#ead5ac]">{{ $templates->total() }}</p><div class="border-l border-white/10 pl-3"><p class="text-xs font-semibold">Total template</p><p class="mt-1 text-[10px] text-white/45">Tersimpan di Memoire</p></div></div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-[#582308]/8 bg-white p-5 shadow-[0_10px_35px_rgba(88,35,8,.04)] sm:p-6">
                        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                            <div><h2 class="font-display text-xl text-[#582308]">Koleksi template</h2><p class="mt-1 text-xs text-[#32170b]/40">Preview, edit file, dan atur status publikasi</p></div>
                            <form method="GET" action="{{ route('admin.templates.index') }}" class="grid gap-2 sm:grid-cols-[minmax(14rem,1fr)_auto_auto]">
                                <label class="relative block"><span class="sr-only">Cari template</span><i class="fa-solid fa-magnifying-glass pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-xs text-[#32170b]/30"></i><input class="h-10 w-full rounded-xl border border-[#582308]/10 bg-[#faf7f0] pl-10 pr-4 text-xs outline-none focus:border-[#bd9150] focus:ring-2 focus:ring-[#bd9150]/10" type="search" name="search" value="{{ request('search') }}" placeholder="Cari judul atau slug..."></label>
                                <button class="h-10 rounded-xl bg-[#582308] px-4 text-xs font-semibold text-white transition hover:bg-[#713719]" type="submit"><i class="fa-solid fa-magnifying-glass mr-2"></i>Cari</button>
                                <a class="grid h-10 place-items-center rounded-xl border border-[#582308]/10 px-4 text-xs font-semibold text-[#582308] transition hover:bg-[#f7f0e5]" href="{{ route('admin.templates.index') }}">Reset</a>
                            </form>
                        </div>
                    </section>

                    <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3" aria-label="Daftar template undangan">
                        @forelse ($templates as $template)
                            <article class="group overflow-hidden rounded-3xl border border-[#582308]/8 bg-white shadow-[0_10px_30px_rgba(88,35,8,.045)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_18px_42px_rgba(88,35,8,.09)]">
                                <div class="relative flex aspect-[16/9] items-center justify-center overflow-hidden bg-[radial-gradient(circle_at_25%_20%,#9b603d_0%,#582308_42%,#301206_100%)] p-6">
                                    <div class="absolute inset-4 rounded-2xl border border-[#ead5ac]/20"></div>
                                    <div class="absolute left-6 top-6 flex items-center gap-2 rounded-full bg-black/20 px-3 py-1.5 text-[9px] font-bold uppercase tracking-[.14em] text-white/75 backdrop-blur"><span class="size-1.5 rounded-full {{ $template->status === 'published' ? 'bg-emerald-400' : 'bg-amber-300' }}"></span>{{ $template->status === 'published' ? 'Published' : 'Draft' }}</div>
                                    <div class="relative text-center text-[#ead5ac]"><i class="fa-regular fa-envelope-open text-3xl opacity-70"></i><p class="mt-3 font-display text-2xl">{{ $template->name }}</p><p class="mt-2 text-[8px] uppercase tracking-[.28em] text-white/40">A home for moments</p></div>
                                    <a href="{{ route('admin.templates.preview', $template) }}" target="_blank" rel="noopener" class="absolute inset-0 grid place-items-center bg-[#32170b]/65 opacity-0 transition group-hover:opacity-100 group-focus-within:opacity-100" aria-label="Preview {{ $template->name }}"><span class="flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-bold text-[#582308]"><i class="fa-regular fa-eye"></i>Preview</span></a>
                                </div>
                                <div class="p-5">
                                    <div class="flex items-start justify-between gap-4"><div class="min-w-0"><h3 class="truncate font-display text-xl text-[#582308]">{{ $template->name }}</h3><p class="mt-1 truncate font-mono text-[10px] text-[#32170b]/40">/{{ $template->slug }}</p></div><span class="shrink-0 text-[10px] text-[#32170b]/35">{{ $template->created_at->format('d M Y') }}</span></div><p class="mt-3 text-[10px] text-[#32170b]/40"><i class="fa-regular fa-envelope mr-1.5 text-[#bd9150]"></i>Dipakai {{ $template->invitations_count }} undangan</p>
                                    <div class="mt-5 flex items-center justify-between gap-3 border-t border-[#582308]/8 pt-4">
                                        <a href="{{ route('admin.templates.edit', $template) }}" class="flex h-9 items-center gap-2 rounded-xl bg-[#f7f0e5] px-4 text-xs font-bold text-[#582308] transition hover:bg-[#ead5ac]/60"><i class="fa-regular fa-pen-to-square"></i>Kelola</a>
                                        <div class="flex items-center gap-1">
                                            <form method="POST" action="{{ route('admin.templates.publish', $template) }}">@csrf @method('PATCH')<button class="grid size-9 place-items-center rounded-lg text-[#32170b]/45 transition hover:bg-[#ead5ac]/35 hover:text-[#582308]" type="submit" title="{{ $template->status === 'published' ? 'Jadikan draft' : 'Publikasikan' }}" aria-label="{{ $template->status === 'published' ? 'Jadikan draft' : 'Publikasikan' }} {{ $template->name }}"><i class="fa-solid {{ $template->status === 'published' ? 'fa-eye-slash' : 'fa-cloud-arrow-up' }} text-xs"></i></button></form>
                                            <form method="POST" action="{{ route('admin.templates.destroy', $template) }}" onsubmit="return confirm('Hapus undangan dan seluruh file templatenya?')">@csrf @method('DELETE')<button class="grid size-9 place-items-center rounded-lg text-[#32170b]/45 transition hover:bg-red-50 hover:text-red-600" type="submit" title="Hapus" aria-label="Hapus {{ $template->name }}"><i class="fa-regular fa-trash-can text-xs"></i></button></form>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-3xl border border-dashed border-[#582308]/15 bg-white/60 px-6 py-16 text-center sm:col-span-2 xl:col-span-3"><span class="mx-auto grid size-14 place-items-center rounded-2xl bg-[#ead5ac]/35 text-xl text-[#582308]/55"><i class="fa-regular fa-folder-open"></i></span><h3 class="mt-4 font-display text-xl text-[#582308]">Template tidak ditemukan</h3><p class="mt-2 text-xs text-[#32170b]/45">Tambahkan template baru atau ubah kata kunci pencarian.</p></div>
                        @endforelse
                    </section>

                    @if ($templates->hasPages())<div class="flex flex-col items-center justify-between gap-4 rounded-2xl border border-[#582308]/8 bg-white px-5 py-4 sm:flex-row"><p class="text-[10px] text-[#32170b]/40">Menampilkan {{ $templates->firstItem() }}–{{ $templates->lastItem() }} dari {{ $templates->total() }} template</p>{{ $templates->links() }}</div>@endif
                </div>
            </main>
        </div>
    </body>
</html>
