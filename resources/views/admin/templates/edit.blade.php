<!DOCTYPE html>
<html lang="id">
    <head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Edit {{ $template->name }} — Memoire Admin</title>@vite('resources/css/app.css')</head>
    <body class="bg-[#f4efe7] text-[#32170b] antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
            @include('admin.partials.sidebar')
            <main class="min-w-0">
                <header class="sticky top-0 z-30 flex h-20 items-center justify-between gap-4 border-b border-[#582308]/10 bg-[#f4efe7]/90 px-5 backdrop-blur-xl sm:px-8 lg:h-24 lg:px-10">
                    <div class="min-w-0"><a href="{{ route('admin.templates.index') }}" class="text-xs text-[#582308]/50 transition hover:text-[#582308]"><i class="fa-solid fa-arrow-left mr-2"></i>Template Undangan</a><h1 class="mt-1 truncate font-display text-2xl text-[#582308] sm:text-3xl">{{ $template->name }}</h1></div>
                    <a href="{{ route('admin.templates.preview', $template) }}" target="_blank" rel="noopener" class="flex h-10 shrink-0 items-center gap-2 rounded-full border border-[#582308]/12 bg-white px-4 text-xs font-semibold text-[#582308] transition hover:bg-[#f7f0e5]"><i class="fa-regular fa-eye"></i><span class="hidden sm:inline">Buka Preview</span></a>
                </header>

                <div class="mx-auto max-w-6xl space-y-6 p-5 sm:p-8 lg:p-10">
                    @if (session('success'))<div role="status" class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800"><i class="fa-solid fa-circle-check"></i>{{ session('success') }}</div>@endif
                    <form method="POST" action="{{ route('admin.templates.update', $template) }}" enctype="multipart/form-data">@csrf @method('PUT') @include('admin.templates.partials.form', ['submitLabel' => 'Simpan perubahan'])</form>

                    <section class="rounded-3xl border border-[#582308]/8 bg-white p-5 shadow-[0_10px_35px_rgba(88,35,8,.04)] sm:p-7">
                        <div class="flex flex-col gap-3 border-b border-[#582308]/8 pb-5 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-[9px] font-bold uppercase tracking-[.18em] text-[#bd9150]">File manager</p><h2 class="mt-2 font-display text-2xl text-[#582308]">Berkas tersimpan</h2><p class="mt-1 text-xs text-[#32170b]/45">{{ count($files) }} file · invitations/{{ $template->folder_name }}</p></div><span class="w-fit rounded-full bg-[#f7f0e5] px-3 py-1.5 text-[10px] font-semibold text-[#582308]"><i class="fa-solid fa-hard-drive mr-1.5"></i>{{ count($files) }} berkas</span></div>
                        <ul class="mt-5 grid gap-3 sm:grid-cols-2">
                            @forelse ($files as $file)
                                @php
                                    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                    $icon = match ($extension) {
                                        'html' => 'fa-brands fa-html5',
                                        'css' => 'fa-brands fa-css3-alt',
                                        'js' => 'fa-brands fa-js',
                                        'jpg', 'jpeg', 'png', 'webp', 'gif', 'svg' => 'fa-regular fa-image',
                                        'mp3', 'wav', 'ogg' => 'fa-solid fa-music',
                                        'mp4' => 'fa-solid fa-film',
                                        default => 'fa-regular fa-file',
                                    };
                                @endphp
                                <li class="flex min-w-0 items-center gap-3 rounded-2xl border border-[#582308]/8 bg-[#faf7f0] p-3"><span class="grid size-10 shrink-0 place-items-center rounded-xl bg-white text-[#582308] shadow-sm"><i class="{{ $icon }}"></i></span><div class="min-w-0 flex-1"><p class="truncate font-mono text-[11px] font-medium text-[#32170b]/70" title="{{ $file }}">{{ $file }}</p><p class="mt-1 text-[9px] uppercase tracking-[.12em] text-[#32170b]/30">{{ $extension ?: 'file' }}</p></div>@if ($file !== 'index.html')<form method="POST" action="{{ route('admin.templates.files.destroy', $template) }}" onsubmit="return confirm('Hapus file {{ $file }}?')">@csrf @method('DELETE')<input type="hidden" name="path" value="{{ $file }}"><button class="grid size-8 shrink-0 place-items-center rounded-lg text-[#32170b]/35 transition hover:bg-red-50 hover:text-red-600" type="submit" title="Hapus file" aria-label="Hapus {{ $file }}"><i class="fa-regular fa-trash-can text-xs"></i></button></form>@else<span class="shrink-0 rounded-full bg-emerald-50 px-2 py-1 text-[8px] font-bold uppercase tracking-[.1em] text-emerald-700">Utama</span>@endif</li>
                            @empty
                                <li class="rounded-2xl border border-dashed border-[#582308]/15 px-5 py-10 text-center sm:col-span-2"><i class="fa-regular fa-folder-open text-2xl text-[#582308]/30"></i><p class="mt-3 text-sm font-semibold text-[#582308]">Folder masih kosong</p><p class="mt-1 text-xs text-[#32170b]/40">Unggah index.html atau satu paket ZIP melalui form di atas.</p></li>
                            @endforelse
                        </ul>
                    </section>
                </div>
            </main>
        </div>
    </body>
</html>
