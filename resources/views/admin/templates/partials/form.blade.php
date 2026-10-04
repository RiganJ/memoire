@php
    $input = 'mt-2 h-12 w-full rounded-xl border border-[#582308]/10 bg-[#faf7f0] px-4 text-sm text-[#32170b] outline-none transition focus:border-[#bd9150] focus:ring-4 focus:ring-[#bd9150]/10';
    $label = 'text-[10px] font-bold uppercase tracking-[.16em] text-[#32170b]/55';
    $fileInput = 'absolute inset-0 z-10 size-full cursor-pointer opacity-0';
@endphp

@if ($errors->any())
    <div class="mb-6 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert"><i class="fa-solid fa-circle-exclamation mt-0.5"></i><div><p class="font-semibold">Template belum dapat disimpan</p><p class="mt-1 text-xs">{{ $errors->first() }}</p></div></div>
@endif

<div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_19rem] xl:items-start">
    <div class="space-y-6">
        <section class="rounded-3xl border border-[#582308]/8 bg-white p-5 shadow-[0_10px_35px_rgba(88,35,8,.04)] sm:p-7">
            <div class="flex items-start gap-4 border-b border-[#582308]/8 pb-5"><span class="grid size-10 shrink-0 place-items-center rounded-xl bg-[#ead5ac]/40 font-display text-[#582308]">01</span><div><h2 class="font-display text-xl text-[#582308]">Identitas template</h2><p class="mt-1 text-xs leading-5 text-[#32170b]/45">Nama internal, alamat publik, dan status penayangan.</p></div></div>
            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <label class="block md:col-span-2"><span class="{{ $label }}">Nama template <b class="text-red-600">*</b></span><input class="{{ $input }}" name="name" value="{{ old('name', $template?->name) }}" maxlength="180" placeholder="Contoh: Midnight Blossom" required>@error('name')<span class="mt-1.5 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
                <label class="block"><span class="{{ $label }}">Slug URL <b class="text-red-600">*</b></span><div class="mt-2 flex h-12 items-center rounded-xl border border-[#582308]/10 bg-[#faf7f0] pl-4 transition focus-within:border-[#bd9150] focus-within:ring-4 focus-within:ring-[#bd9150]/10"><span class="shrink-0 text-sm text-[#32170b]/35">/</span><input class="h-full min-w-0 flex-1 bg-transparent px-2 text-sm outline-none" name="slug" value="{{ old('slug', $template?->slug) }}" maxlength="100" placeholder="midnight-blossom" required></div>@error('slug')<span class="mt-1.5 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
                <label class="block"><span class="{{ $label }}">Status publikasi</span><select class="{{ $input }}" name="status"><option value="draft" @selected(old('status', $template?->status ?? 'draft') === 'draft')>Draft — hanya admin</option><option value="published" @selected(old('status', $template?->status) === 'published')>Published — dapat diakses publik</option></select>@error('status')<span class="mt-1.5 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
            </div>
            <div class="mt-5 space-y-2 rounded-xl bg-[#faf7f0] p-4 text-[11px]">
                <div class="flex items-center gap-3 text-[#32170b]/55"><i class="fa-solid fa-user-shield w-4 text-center text-[#bd9150]"></i><span class="w-24 shrink-0 font-semibold">Preview admin</span>@if ($template)<a href="{{ route('admin.templates.preview', $template) }}" target="_blank" rel="noopener" class="min-w-0 truncate text-[#582308] underline decoration-[#bd9150]/40 underline-offset-2">{{ route('admin.templates.preview', $template) }}</a>@else<span class="text-[#32170b]/35">Tersedia setelah template disimpan</span>@endif</div>
                <div class="flex items-center gap-3 text-[#32170b]/55"><i class="fa-solid fa-users-viewfinder w-4 text-center text-[#bd9150]"></i><span class="w-24 shrink-0 font-semibold">Preview customer</span>@if ($template && $template->status === 'published')<a href="{{ route('customer.templates.preview', $template) }}" target="_blank" rel="noopener" class="min-w-0 truncate text-[#582308] underline decoration-[#bd9150]/40 underline-offset-2" data-customer-preview-link>{{ route('customer.templates.preview', $template) }}</a>@else<span class="min-w-0 truncate text-[#32170b]/35" data-customer-preview-url>{{ url('/preview-customer') }}/<b data-slug-preview>{{ old('slug', $template?->slug ?? 'nama-template') }}</b>{{ $template ? ' · aktif setelah Published' : ' · aktif setelah disimpan sebagai Published' }}</span>@endif</div>
            </div>
        </section>

        <section class="rounded-3xl border border-[#582308]/8 bg-white p-5 shadow-[0_10px_35px_rgba(88,35,8,.04)] sm:p-7">
            <div class="flex items-start gap-4 border-b border-[#582308]/8 pb-5"><span class="grid size-10 shrink-0 place-items-center rounded-xl bg-[#ead5ac]/40 font-display text-[#582308]">02</span><div><h2 class="font-display text-xl text-[#582308]">Berkas template</h2><p class="mt-1 text-xs leading-5 text-[#32170b]/45">Unggah berkas terpisah untuk kontrol penuh atas struktur template.</p></div></div>
            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <label class="group relative min-h-40 overflow-hidden rounded-2xl border border-dashed border-[#582308]/20 bg-[#faf7f0] p-5 transition hover:border-[#bd9150] hover:bg-[#f8f0e2]"><input class="{{ $fileInput }}" type="file" name="index_html" accept=".html,text/html"><span class="grid size-10 place-items-center rounded-xl bg-white text-[#582308] shadow-sm"><i class="fa-brands fa-html5"></i></span><span class="mt-4 block text-sm font-bold text-[#582308]">Halaman utama</span><span class="mt-1 block text-[11px] leading-5 text-[#32170b]/45">Pilih index.html · maksimal 5 MB</span>@error('index_html')<span class="relative z-20 mt-2 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
                <label class="group relative min-h-40 overflow-hidden rounded-2xl border border-dashed border-[#582308]/20 bg-[#faf7f0] p-5 transition hover:border-[#bd9150] hover:bg-[#f8f0e2]"><input class="{{ $fileInput }}" type="file" name="css_files[]" accept=".css,text/css" multiple><span class="grid size-10 place-items-center rounded-xl bg-white text-[#582308] shadow-sm"><i class="fa-brands fa-css3-alt"></i></span><span class="mt-4 block text-sm font-bold text-[#582308]">Stylesheet CSS</span><span class="mt-1 block text-[11px] leading-5 text-[#32170b]/45">Bisa pilih beberapa · disimpan ke css/</span>@error('css_files.*')<span class="relative z-20 mt-2 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
                <label class="group relative min-h-40 overflow-hidden rounded-2xl border border-dashed border-[#582308]/20 bg-[#faf7f0] p-5 transition hover:border-[#bd9150] hover:bg-[#f8f0e2]"><input class="{{ $fileInput }}" type="file" name="js_files[]" accept=".js,text/javascript" multiple><span class="grid size-10 place-items-center rounded-xl bg-white text-[#582308] shadow-sm"><i class="fa-brands fa-js"></i></span><span class="mt-4 block text-sm font-bold text-[#582308]">JavaScript</span><span class="mt-1 block text-[11px] leading-5 text-[#32170b]/45">Bisa pilih beberapa · disimpan ke js/</span>@error('js_files.*')<span class="relative z-20 mt-2 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
                <label class="group relative min-h-40 overflow-hidden rounded-2xl border border-dashed border-[#582308]/20 bg-[#faf7f0] p-5 transition hover:border-[#bd9150] hover:bg-[#f8f0e2]"><input class="{{ $fileInput }}" type="file" name="asset_files[]" accept=".jpg,.jpeg,.png,.webp,.gif,.svg,.mp3,.wav,.ogg,.mp4,.webm,.woff,.woff2,.ttf,.json" multiple webkitdirectory directory data-asset-folder><span class="grid size-10 place-items-center rounded-xl bg-white text-[#582308] shadow-sm"><i class="fa-regular fa-folder-open"></i></span><span class="mt-4 block text-sm font-bold text-[#582308]">Folder assets</span><span class="mt-1 block text-[11px] leading-5 text-[#32170b]/45">Gambar, MP4/WebM, audio, font · struktur subfolder tetap</span><p class="relative z-20 mt-2 hidden text-[11px] text-amber-700" data-asset-feedback></p><div data-asset-paths></div>@error('asset_files.*')<span class="relative z-20 mt-2 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
            </div>

            <details class="group mt-5 rounded-2xl border border-[#582308]/10 bg-[#faf7f0] p-4 sm:p-5"><summary class="flex cursor-pointer list-none items-center justify-between gap-3 [&::-webkit-details-marker]:hidden"><span class="flex items-center gap-3"><span class="grid size-9 place-items-center rounded-xl bg-[#ead5ac]/40 text-[#582308]"><i class="fa-solid fa-file-zipper text-sm"></i></span><span><b class="block text-xs text-[#582308]">Punya satu paket ZIP?</b><small class="mt-1 block text-[10px] text-[#32170b]/40">Gunakan sebagai alternatif upload terpisah</small></span></span><i class="fa-solid fa-chevron-down text-xs text-[#582308]/40 transition group-open:rotate-180"></i></summary><label class="mt-4 block border-t border-[#582308]/8 pt-4"><span class="{{ $label }}">Template ZIP</span><input class="mt-2 block w-full rounded-xl border border-[#582308]/10 bg-white px-4 py-3 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-[#ead5ac]/50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-[#582308]" type="file" name="template_zip" accept=".zip,application/zip"><span class="mt-1.5 block text-[11px] text-[#32170b]/40">Wajib memiliki index.html di folder utama · maksimal 20 MB.</span>@error('template_zip')<span class="mt-1.5 block text-xs text-red-600">{{ $message }}</span>@enderror</label></details>
        </section>
    </div>

    <aside class="space-y-4 xl:sticky xl:top-28">
        <section class="rounded-3xl bg-[#582308] p-6 text-white shadow-[0_16px_38px_rgba(88,35,8,.16)]"><p class="text-[9px] font-bold uppercase tracking-[.2em] text-[#ead5ac]/70">Sebelum menyimpan</p><h2 class="mt-3 font-display text-2xl text-[#ead5ac]">Checklist template</h2><ul class="mt-5 space-y-3 text-xs text-white/65"><li class="flex gap-3"><i class="fa-solid fa-check mt-0.5 text-[#ead5ac]"></i><span>Pastikan index.html berada di folder utama.</span></li><li class="flex gap-3"><i class="fa-solid fa-check mt-0.5 text-[#ead5ac]"></i><span>Gunakan path relatif untuk CSS, JS, dan assets.</span></li><li class="flex gap-3"><i class="fa-solid fa-check mt-0.5 text-[#ead5ac]"></i><span>Maksimal 25 MB per file dan 100 MB total assets.</span></li><li class="flex gap-3"><i class="fa-solid fa-check mt-0.5 text-[#ead5ac]"></i><span>Simpan sebagai draft jika belum siap dipublikasikan.</span></li></ul></section>
        <section class="rounded-3xl border border-[#582308]/8 bg-white p-5 shadow-[0_10px_30px_rgba(88,35,8,.04)]"><button class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#582308] px-5 text-sm font-semibold text-white transition hover:bg-[#713719] disabled:cursor-wait disabled:opacity-60" type="submit" data-invitation-submit><i class="fa-solid fa-floppy-disk"></i>{{ $submitLabel }}</button><a href="{{ route('admin.templates.index') }}" class="mt-2 grid h-11 place-items-center rounded-xl border border-[#582308]/10 text-xs font-semibold text-[#582308] transition hover:bg-[#f7f0e5]">Batal</a></section>
    </aside>
</div>

@once
    <script>
        const invitationSlugInput = document.querySelector('input[name="slug"]');
        const invitationSlugPreviews = document.querySelectorAll('[data-slug-preview]');
        const invitationAssetInput = document.querySelector('[data-asset-folder]');
        const invitationAssetPaths = document.querySelector('[data-asset-paths]');
        const invitationAssetFeedback = document.querySelector('[data-asset-feedback]');
        const invitationSubmit = document.querySelector('[data-invitation-submit]');
        const allowedAssetExtensions = new Set(['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'mp3', 'wav', 'ogg', 'mp4', 'webm', 'woff', 'woff2', 'ttf', 'json']);
        invitationSlugInput?.addEventListener('input', () => {
            const slug = invitationSlugInput.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
            invitationSlugPreviews.forEach((preview) => { preview.textContent = slug; });
        });
        invitationAssetInput?.addEventListener('change', () => {
            const uploads = new DataTransfer();
            const skipped = [];
            Array.from(invitationAssetInput.files).forEach((file) => {
                const path = file.webkitRelativePath || file.name;
                const extension = file.name.includes('.') ? file.name.split('.').pop().toLowerCase() : '';
                const hasHiddenPart = path.split('/').some((part) => part.startsWith('.'));
                if (hasHiddenPart || !allowedAssetExtensions.has(extension)) { skipped.push(path); return; }
                uploads.items.add(file);
            });
            invitationAssetInput.files = uploads.files;
            invitationAssetFeedback.classList.toggle('hidden', skipped.length === 0);
            invitationAssetFeedback.textContent = skipped.length ? `${skipped.length} file tidak didukung diabaikan: ${skipped.slice(0, 3).join(', ')}${skipped.length > 3 ? '…' : ''}` : `${uploads.files.length} file siap diunggah.`;
            invitationAssetFeedback.classList.toggle('text-amber-700', skipped.length > 0);
            invitationAssetFeedback.classList.toggle('text-emerald-700', skipped.length === 0 && uploads.files.length > 0);
            invitationAssetFeedback.classList.toggle('hidden', skipped.length === 0 && uploads.files.length === 0);
            invitationAssetPaths.replaceChildren(...Array.from(invitationAssetInput.files, (file) => {
                const path = document.createElement('input'); path.type = 'hidden'; path.name = 'asset_paths[]'; path.value = file.webkitRelativePath || file.name; return path;
            }));
        });
        invitationSubmit?.form?.addEventListener('submit', () => {
            invitationSubmit.disabled = true;
            invitationSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>Menyimpan template...';
        });
    </script>
@endonce
