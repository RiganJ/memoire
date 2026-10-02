<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Katalog Desain Memoire">
        <title>Katalog — Memoire Admin</title>
        @vite('resources/css/app.css')
    </head>
    <body class="bg-[#f4efe7] text-[#32170b] antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
            @include('admin.partials.sidebar')

            <main class="min-w-0">
                <header class="sticky top-0 z-30 flex h-20 items-center justify-between border-b border-[#582308]/10 bg-[#f4efe7]/90 px-5 backdrop-blur-xl sm:px-8 lg:h-24 lg:px-10">
                    <div><p class="text-xs text-[#32170b]/45">Kelola koleksi</p><h1 class="mt-1 font-display text-2xl text-[#582308] sm:text-3xl">Katalog Desain</h1></div>
                    <a href="{{ route('admin.catalog.create') }}" class="flex h-11 items-center gap-2 rounded-full bg-[#582308] px-4 text-xs font-semibold text-white sm:px-5 sm:text-sm"><i class="fa-solid fa-plus text-xs"></i><span class="hidden sm:inline">Desain Baru</span></a>
                </header>

                <div class="space-y-6 p-5 sm:p-8 lg:p-10">
                    @if (session('success'))
                        <p role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</p>
                    @endif

                    <section class="grid grid-cols-2 gap-3 xl:grid-cols-4" aria-label="Ringkasan katalog">
                        @foreach ([[$summary['all'], 'Total desain', 'fa-swatchbook'], [$summary['active'], 'Desain aktif', 'fa-circle-check'], [$summary['draft'], 'Dalam draft', 'fa-pen-ruler'], [$summary['archived'], 'Diarsipkan', 'fa-box-archive']] as [$value, $label, $icon])
                            <article class="flex items-center gap-4 rounded-2xl border border-[#582308]/8 bg-white p-4 shadow-[0_8px_25px_rgba(88,35,8,.035)] sm:p-5"><span class="hidden size-11 shrink-0 place-items-center rounded-xl bg-[#ead5ac]/45 text-[#582308] sm:grid"><i class="fa-solid {{ $icon }} text-sm"></i></span><div><p class="font-display text-2xl text-[#582308]">{{ $value }}</p><p class="mt-1 text-[10px] text-[#32170b]/45 sm:text-xs">{{ $label }}</p></div></article>
                        @endforeach
                    </section>

                    <section class="rounded-3xl border border-[#582308]/8 bg-white p-5 shadow-[0_10px_35px_rgba(88,35,8,.04)] sm:p-6">
                        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                            <div><h2 class="font-display text-xl text-[#582308]">Koleksi Memoire</h2><p class="mt-1 text-xs text-[#32170b]/40">Atur desain, kategori, paket, dan publikasi</p></div>
                            <form method="GET" action="{{ route('admin.catalog.index') }}" class="grid gap-2 sm:grid-cols-[minmax(12rem,1fr)_auto_auto] xl:w-auto">
                                <label class="relative block"><span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-[#32170b]/30"><i class="fa-solid fa-magnifying-glass text-xs"></i></span><input type="search" name="search" value="{{ request('search') }}" placeholder="Cari desain..." class="h-10 w-full rounded-xl border border-[#582308]/10 bg-[#faf7f0] pl-10 pr-4 text-xs outline-none focus:border-[#bd9150]"></label>
                                <select name="category" aria-label="Filter kategori" class="h-10 rounded-xl border border-[#582308]/10 bg-[#faf7f0] px-3 text-xs text-[#32170b]/65 outline-none focus:border-[#bd9150]"><option value="">Semua kategori</option>@foreach ($categories as $category)<option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>@endforeach</select>
                                <select name="status" aria-label="Filter status" class="h-10 rounded-xl border border-[#582308]/10 bg-[#faf7f0] px-3 text-xs text-[#32170b]/65 outline-none focus:border-[#bd9150]"><option value="">Semua status</option><option value="active" @selected(request('status') === 'active')>Aktif</option><option value="draft" @selected(request('status') === 'draft')>Draft</option><option value="archived" @selected(request('status') === 'archived')>Diarsipkan</option></select>
                                <div class="flex gap-2 sm:col-span-3 xl:col-span-1"><button type="submit" class="h-10 rounded-xl bg-[#582308] px-4 text-xs font-semibold text-white"><i class="fa-solid fa-filter mr-2"></i>Filter</button><a href="{{ route('admin.catalog.index') }}" class="grid h-10 place-items-center rounded-xl border border-[#582308]/10 px-4 text-xs font-semibold text-[#582308]">Reset</a></div>
                            </form>
                        </div>
                    </section>

                    <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4" aria-label="Daftar desain">
                        @forelse ($catalogs as $catalog)
                            @php($statusLabels = ['active' => 'Aktif', 'draft' => 'Draft', 'archived' => 'Diarsipkan'])
                            <article class="group overflow-hidden rounded-3xl border border-[#582308]/8 bg-white shadow-[0_10px_30px_rgba(88,35,8,.045)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_18px_42px_rgba(88,35,8,.09)]">
                                <div class="relative aspect-[4/3] overflow-hidden p-5" style="background-color: {{ $catalog->color }}">
                                    @if ($catalog->image_path)<img src="{{ asset('storage/'.$catalog->image_path) }}" alt="Preview {{ $catalog->name }}" class="catalog-admin-preview absolute inset-0 size-full object-cover">@endif
                                    <div class="absolute inset-0 {{ $catalog->image_path ? 'bg-[#32170b]/35' : 'opacity-20 [background-image:radial-gradient(circle_at_20%_15%,white_0,transparent_32%),radial-gradient(circle_at_85%_85%,white_0,transparent_28%)]' }}"></div>
                                    <div class="relative flex items-start justify-between"><span class="rounded-full bg-white/85 px-3 py-1.5 text-[9px] font-bold uppercase tracking-[.15em] text-[#582308]">{{ $catalog->category }}</span><span class="grid size-8 place-items-center rounded-full bg-black/15 text-white backdrop-blur" aria-hidden="true"><i class="fa-solid fa-layer-group text-xs"></i></span></div>
                                    <div class="absolute inset-x-0 bottom-0 top-11 flex items-center justify-center"><div class="flex aspect-[3/4] h-[80%] flex-col items-center justify-between border border-white/50 bg-white/10 p-4 text-center text-white shadow-2xl backdrop-blur-[2px] transition duration-500 group-hover:-translate-y-1 group-hover:rotate-1"><p class="text-[5px] uppercase tracking-[.3em]">Memoire collection</p><p class="font-display text-3xl italic">{{ mb_strtoupper(mb_substr($catalog->name, 0, 2)) }}</p><div><p class="font-display text-xs">{{ $catalog->name }}</p><p class="mt-1 text-[5px] uppercase tracking-[.2em]">A home for moments</p></div></div></div>
                                </div>
                                <div class="p-5">
                                    <div class="flex items-start justify-between gap-3"><div><h3 class="font-display text-xl text-[#582308]">{{ $catalog->name }}</h3><p class="mt-1 text-[10px] text-[#32170b]/40">{{ $catalog->package }} · Dibuat {{ $catalog->created_at->format('d M Y') }}</p></div><span class="catalog-status catalog-status-{{ $catalog->status }}"><span></span>{{ $statusLabels[$catalog->status] }}</span></div>
                                    <div class="mt-5 flex justify-end border-t border-[#582308]/8 pt-4"><div class="flex gap-1"><a href="{{ route('admin.catalog.edit', $catalog) }}" class="grid size-8 place-items-center rounded-lg text-[#32170b]/45 transition hover:bg-[#ead5ac]/40 hover:text-[#582308]" aria-label="Edit {{ $catalog->name }}" title="Edit desain"><i class="fa-regular fa-pen-to-square text-xs"></i></a><form action="{{ route('admin.catalog.destroy', $catalog) }}" method="POST" onsubmit="return confirm('Hapus desain ini?')">@csrf @method('DELETE')<button type="submit" class="grid size-8 place-items-center rounded-lg text-[#32170b]/45 transition hover:bg-red-50 hover:text-red-600" aria-label="Hapus {{ $catalog->name }}" title="Hapus desain"><i class="fa-regular fa-trash-can text-xs"></i></button></form></div></div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-2xl border border-dashed border-[#582308]/15 bg-white px-5 py-12 text-center sm:col-span-2 xl:col-span-3 2xl:col-span-4"><i class="fa-regular fa-folder-open text-2xl text-[#582308]/35"></i><p class="mt-3 font-display text-lg text-[#582308]">Desain tidak ditemukan</p><p class="mt-1 text-xs text-[#32170b]/45">Coba ubah filter atau tambahkan desain baru.</p></div>
                        @endforelse
                    </section>

                    <div class="flex flex-col items-center justify-between gap-4 rounded-2xl border border-[#582308]/8 bg-white px-5 py-4 sm:flex-row"><p class="text-[10px] text-[#32170b]/40">Menampilkan {{ $catalogs->firstItem() ?? 0 }}-{{ $catalogs->lastItem() ?? 0 }} dari {{ $catalogs->total() }} desain</p>{{ $catalogs->links() }}</div>
                </div>
            </main>
        </div>
    </body>
</html>
