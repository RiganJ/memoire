@php
    $input = 'mt-2 h-12 w-full rounded-xl border border-[#582308]/12 bg-[#fcfaf6] px-4 text-sm text-[#32170b] shadow-[inset_0_1px_0_rgba(255,255,255,.8)] outline-none transition placeholder:text-[#32170b]/30 hover:border-[#582308]/20 focus:border-[#bd9150] focus:bg-white focus:ring-4 focus:ring-[#bd9150]/10';
    $label = 'text-[10px] font-bold uppercase tracking-[.16em] text-[#32170b]/55';
    $fieldError = 'mt-1.5 flex items-center gap-1.5 text-xs text-red-600';
    $selectedTemplate = $templates->firstWhere('id', (int) old('template_id', $invitation?->template_id));
@endphp

@if ($errors->any())
    <div class="mb-6 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
        <span class="grid size-8 shrink-0 place-items-center rounded-full bg-red-100"><i class="fa-solid fa-circle-exclamation text-xs"></i></span>
        <div>
            <p class="font-semibold">Data belum dapat disimpan</p>
            <p class="mt-0.5 text-xs text-red-600/80">Periksa kembali field yang ditandai di bawah.</p>
        </div>
    </div>
@endif

<div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_19rem]">
    <div class="space-y-6">
        <section class="overflow-hidden rounded-3xl border border-[#582308]/8 bg-white shadow-[0_16px_45px_rgba(88,35,8,.055)]">
            <div class="flex items-start gap-4 border-b border-[#582308]/8 px-5 py-5 sm:px-7">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-[#ead5ac]/40 text-[#582308]"><i class="fa-regular fa-heart text-sm"></i></span>
                <div>
                    <h2 class="font-display text-xl text-[#582308]">Informasi pasangan</h2>
                    <p class="mt-1 text-xs leading-5 text-[#32170b]/45">Informasi utama yang akan tampil pada undangan digital.</p>
                </div>
            </div>

            <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-7">
                <label class="block sm:col-span-2">
                    <span class="{{ $label }}">Nama undangan <b class="text-red-500">*</b></span>
                    <input class="{{ $input }}" name="name" value="{{ old('name', $invitation?->name) }}" maxlength="180" placeholder="Contoh: Rigan & Salsa" autocomplete="off" required>
                    @error('name')<span class="{{ $fieldError }}"><i class="fa-solid fa-circle-exclamation text-[10px]"></i>{{ $message }}</span>@enderror
                </label>

                <label class="block">
                    <span class="{{ $label }}">Nama pengantin pria <b class="text-red-500">*</b></span>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-2 left-0 grid w-11 place-items-center border-r border-[#582308]/8 text-[#582308]/30"><i class="fa-regular fa-user text-xs"></i></span>
                        <input class="{{ $input }} pl-14" name="groom_name" value="{{ old('groom_name', $invitation?->groom_name) }}" maxlength="120" placeholder="Nama lengkap" autocomplete="off" required>
                    </div>
                    @error('groom_name')<span class="{{ $fieldError }}"><i class="fa-solid fa-circle-exclamation text-[10px]"></i>{{ $message }}</span>@enderror
                </label>

                <label class="block">
                    <span class="{{ $label }}">Nama pengantin wanita <b class="text-red-500">*</b></span>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-2 left-0 grid w-11 place-items-center border-r border-[#582308]/8 text-[#582308]/30"><i class="fa-regular fa-user text-xs"></i></span>
                        <input class="{{ $input }} pl-14" name="bride_name" value="{{ old('bride_name', $invitation?->bride_name) }}" maxlength="120" placeholder="Nama lengkap" autocomplete="off" required>
                    </div>
                    @error('bride_name')<span class="{{ $fieldError }}"><i class="fa-solid fa-circle-exclamation text-[10px]"></i>{{ $message }}</span>@enderror
                </label>
            </div>
        </section>

        <section class="overflow-hidden rounded-3xl border border-[#582308]/8 bg-white shadow-[0_16px_45px_rgba(88,35,8,.055)]">
            <div class="flex items-start gap-4 border-b border-[#582308]/8 px-5 py-5 sm:px-7">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-[#ead5ac]/40 text-[#582308]"><i class="fa-solid fa-sliders text-sm"></i></span>
                <div>
                    <h2 class="font-display text-xl text-[#582308]">Pengaturan undangan</h2>
                    <p class="mt-1 text-xs leading-5 text-[#32170b]/45">Tentukan alamat publik, desain, dan status publikasi.</p>
                </div>
            </div>

            <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-7">
                <label class="block sm:col-span-2">
                    <span class="{{ $label }}">Slug URL <b class="text-red-500">*</b></span>
                    <div class="mt-2 flex h-12 items-center overflow-hidden rounded-xl border border-[#582308]/12 bg-[#fcfaf6] transition focus-within:border-[#bd9150] focus-within:bg-white focus-within:ring-4 focus-within:ring-[#bd9150]/10">
                        <span class="flex h-full shrink-0 items-center border-r border-[#582308]/8 bg-[#f6f0e6] px-4 text-xs font-medium text-[#32170b]/45">{{ rtrim(url('/'), '/') }}/</span>
                        <input class="min-w-28 flex-1 bg-transparent px-3 text-sm text-[#32170b] outline-none placeholder:text-[#32170b]/30" name="slug" value="{{ old('slug', $invitation?->slug) }}" maxlength="100" placeholder="rigan-salsa" autocomplete="off" required>
                    </div>
                    <p class="mt-2 flex items-center gap-1.5 text-[11px] text-[#32170b]/40"><i class="fa-solid fa-link text-[9px]"></i>Gunakan huruf kecil, angka, dan tanda hubung.</p>
                    @error('slug')<span class="{{ $fieldError }}"><i class="fa-solid fa-circle-exclamation text-[10px]"></i>{{ $message }}</span>@enderror
                </label>

                <label class="block">
                    <span class="{{ $label }}">Template <b class="text-red-500">*</b></span>
                    <select class="{{ $input }}" name="template_id" required>
                        <option value="">Pilih template published</option>
                        @foreach ($templates as $template)
                            <option value="{{ $template->id }}" @selected((string) old('template_id', $invitation?->template_id) === (string) $template->id)>{{ $template->name }}</option>
                        @endforeach
                    </select>
                    @error('template_id')<span class="{{ $fieldError }}"><i class="fa-solid fa-circle-exclamation text-[10px]"></i>{{ $message }}</span>@enderror
                </label>

                <label class="block">
                    <span class="{{ $label }}">Status publikasi</span>
                    <select class="{{ $input }}" name="status" required>
                        <option value="draft" @selected(old('status', $invitation?->status ?? 'draft') === 'draft')>Draft — belum publik</option>
                        <option value="published" @selected(old('status', $invitation?->status) === 'published')>Published — dapat diakses</option>
                    </select>
                    @error('status')<span class="{{ $fieldError }}"><i class="fa-solid fa-circle-exclamation text-[10px]"></i>{{ $message }}</span>@enderror
                </label>
            </div>
        </section>

        @if (! $invitation)
            <section class="overflow-hidden rounded-3xl border border-[#582308]/8 bg-white shadow-[0_16px_45px_rgba(88,35,8,.055)]">
                <div class="flex flex-col gap-4 border-b border-[#582308]/8 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                    <div class="flex items-start gap-4">
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-[#ead5ac]/40 text-[#582308]"><i class="fa-solid fa-users text-sm"></i></span>
                        <div>
                            <h2 class="font-display text-xl text-[#582308]">Data tamu</h2>
                            <p class="mt-1 text-xs leading-5 text-[#32170b]/45">Cukup masukkan nama dan nomor WhatsApp. Link personal dibuat otomatis.</p>
                        </div>
                    </div>
                    <button type="button" data-add-guest class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-full border border-[#582308]/12 px-4 text-xs font-semibold text-[#582308] transition hover:bg-[#faf7f0]"><i class="fa-solid fa-plus"></i>Tambah tamu</button>
                </div>

                <div class="space-y-3 p-5 sm:p-7" data-guest-list>
                    @foreach (old('guests', [['name' => '', 'phone' => '']]) as $guestIndex => $guest)
                        <div class="grid gap-3 rounded-2xl border border-[#582308]/8 bg-[#fcfaf6] p-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end" data-guest-row>
                            <label class="block">
                                <span class="{{ $label }}">Nama tamu <b class="text-red-500">*</b></span>
                                <input class="{{ $input }}" name="guests[{{ $guestIndex }}][name]" value="{{ $guest['name'] ?? '' }}" data-guest-field="name" maxlength="180" placeholder="Budi & Partner" autocomplete="off">
                                @error("guests.{$guestIndex}.name")<span class="{{ $fieldError }}"><i class="fa-solid fa-circle-exclamation text-[10px]"></i>{{ $message }}</span>@enderror
                            </label>
                            <label class="block">
                                <span class="{{ $label }}">Nomor WhatsApp <b class="text-red-500">*</b></span>
                                <input class="{{ $input }}" type="tel" inputmode="tel" name="guests[{{ $guestIndex }}][phone]" value="{{ $guest['phone'] ?? '' }}" data-guest-field="phone" maxlength="25" placeholder="08123456789" autocomplete="tel">
                                @error("guests.{$guestIndex}.phone")<span class="{{ $fieldError }}"><i class="fa-solid fa-circle-exclamation text-[10px]"></i>{{ $message }}</span>@enderror
                            </label>
                            <button type="button" data-remove-guest class="grid size-12 place-items-center rounded-xl border border-red-200 text-red-600 transition hover:bg-red-50" aria-label="Hapus baris tamu"><i class="fa-regular fa-trash-can"></i></button>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    <aside class="space-y-4 xl:sticky xl:top-32">
        <section class="overflow-hidden rounded-3xl bg-[#582308] text-white shadow-[0_18px_45px_rgba(88,35,8,.18)]">
            <div class="relative overflow-hidden p-6">
                <div class="pointer-events-none absolute -right-10 -top-12 size-40 rounded-full border border-white/10"></div>
                <div class="pointer-events-none absolute -right-4 -top-2 size-24 rounded-full border border-white/10"></div>
                <p class="relative text-[9px] font-bold uppercase tracking-[.2em] text-[#ead5ac]/65">Ringkasan</p>
                <div class="relative mt-8 grid size-12 place-items-center rounded-2xl border border-white/10 bg-white/8 text-[#ead5ac]"><i class="fa-regular fa-envelope-open"></i></div>
                <h3 id="invitation-summary-name" class="relative mt-5 font-display text-2xl leading-tight text-[#ead5ac]">{{ old('name', $invitation?->name) ?: 'Nama Undangan' }}</h3>
                <p class="relative mt-2 text-xs text-white/45"><span id="invitation-summary-groom">{{ old('groom_name', $invitation?->groom_name) ?: 'Pengantin pria' }}</span> & <span id="invitation-summary-bride">{{ old('bride_name', $invitation?->bride_name) ?: 'Pengantin wanita' }}</span></p>
            </div>

            <dl class="divide-y divide-white/8 border-t border-white/10 bg-black/5 px-6">
                <div class="flex items-center justify-between gap-3 py-4">
                    <dt class="text-[10px] uppercase tracking-[.12em] text-white/35">Status</dt>
                    <dd id="invitation-summary-status" class="rounded-full bg-amber-400/15 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider text-amber-200">Draft</dd>
                </div>
                <div class="py-4">
                    <dt class="text-[10px] uppercase tracking-[.12em] text-white/35">Alamat undangan</dt>
                    <dd id="invitation-summary-url" class="mt-2 break-all font-mono text-[10px] leading-5 text-white/65">{{ rtrim(url('/'), '/') }}/{{ old('slug', $invitation?->slug) ?: 'slug-undangan' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-3 py-4">
                    <dt class="text-[10px] uppercase tracking-[.12em] text-white/35">Template</dt>
                    <dd id="invitation-summary-template" class="max-w-40 truncate text-xs font-semibold text-[#ead5ac]">{{ $selectedTemplate?->name ?: 'Belum dipilih' }}</dd>
                </div>
            </dl>
        </section>

        <div class="rounded-2xl border border-[#582308]/8 bg-white/65 p-4 text-xs leading-5 text-[#32170b]/50">
            <div class="flex gap-3">
                <i class="fa-regular fa-lightbulb mt-0.5 text-[#bd9150]"></i>
                <p>Simpan sebagai <strong class="text-[#582308]">Draft</strong> jika data dan daftar tamu belum siap dipublikasikan.</p>
            </div>
        </div>
    </aside>
</div>

<div class="mt-6 flex flex-col-reverse gap-3 rounded-2xl border border-[#582308]/8 bg-white/75 p-3 shadow-[0_10px_30px_rgba(88,35,8,.04)] backdrop-blur sm:flex-row sm:items-center sm:justify-between sm:px-4">
    <p class="hidden items-center gap-2 text-[11px] text-[#32170b]/40 sm:flex"><i class="fa-solid fa-shield-halved text-[#bd9150]"></i>Perubahan tersimpan setelah tombol simpan ditekan.</p>
    <div class="flex flex-col-reverse gap-2 sm:flex-row">
        <a href="{{ $invitation ? route('admin.invitations.show', $invitation) : route('admin.invitations.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-[#582308]/12 px-5 text-sm font-semibold text-[#582308] transition hover:border-[#582308]/25 hover:bg-[#faf7f0]">Batal</a>
        <button class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#582308] px-6 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(88,35,8,.16)] transition hover:-translate-y-0.5 hover:bg-[#713719]" type="submit">
            <i class="fa-regular fa-floppy-disk"></i>
            {{ $submitLabel }}
        </button>
    </div>
</div>

@once
    <script>
        (() => {
            const name = document.querySelector('input[name="name"]');
            const groom = document.querySelector('input[name="groom_name"]');
            const bride = document.querySelector('input[name="bride_name"]');
            const slug = document.querySelector('input[name="slug"]');
            const status = document.querySelector('select[name="status"]');
            const template = document.querySelector('select[name="template_id"]');
            const appUrl = @json(rtrim(url('/'), '/'));
            let slugEdited = Boolean(slug?.value.length);

            const setText = (id, value, fallback) => {
                const element = document.getElementById(id);
                if (element) element.textContent = value || fallback;
            };

            const updateSummary = () => {
                setText('invitation-summary-name', name?.value, 'Nama Undangan');
                setText('invitation-summary-groom', groom?.value, 'Pengantin pria');
                setText('invitation-summary-bride', bride?.value, 'Pengantin wanita');
                setText('invitation-summary-url', `${appUrl}/${slug?.value || 'slug-undangan'}`, '');
                setText('invitation-summary-template', template?.selectedOptions[0]?.value ? template.selectedOptions[0].text : '', 'Belum dipilih');

                const statusBadge = document.getElementById('invitation-summary-status');
                if (statusBadge) {
                    const published = status?.value === 'published';
                    statusBadge.textContent = published ? 'Published' : 'Draft';
                    statusBadge.className = published
                        ? 'rounded-full bg-emerald-400/15 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider text-emerald-200'
                        : 'rounded-full bg-amber-400/15 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider text-amber-200';
                }
            };

            slug?.addEventListener('input', () => {
                slugEdited = slug.value.length > 0;
                updateSummary();
            });

            name?.addEventListener('input', () => {
                if (!slugEdited && slug) {
                    slug.value = name.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
                }
                updateSummary();
            });

            [groom, bride, status, template].forEach((field) => field?.addEventListener('input', updateSummary));
            updateSummary();
        })();
    </script>
@endonce

@if (! $invitation)
    <template id="guest-row-template">
        <div class="grid gap-3 rounded-2xl border border-[#582308]/8 bg-[#fcfaf6] p-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end" data-guest-row>
            <label class="block">
                <span class="{{ $label }}">Nama tamu <b class="text-red-500">*</b></span>
                <input class="{{ $input }}" data-guest-field="name" maxlength="180" placeholder="Budi & Partner" autocomplete="off">
            </label>
            <label class="block">
                <span class="{{ $label }}">Nomor WhatsApp <b class="text-red-500">*</b></span>
                <input class="{{ $input }}" type="tel" inputmode="tel" data-guest-field="phone" maxlength="25" placeholder="08123456789" autocomplete="tel">
            </label>
            <button type="button" data-remove-guest class="grid size-12 place-items-center rounded-xl border border-red-200 text-red-600 transition hover:bg-red-50" aria-label="Hapus baris tamu"><i class="fa-regular fa-trash-can"></i></button>
        </div>
    </template>
    <script>
        (() => {
            const guestList = document.querySelector('[data-guest-list]');
            const guestTemplate = document.getElementById('guest-row-template');
            const addGuestButton = document.querySelector('[data-add-guest]');

            const reindexGuests = () => {
                guestList?.querySelectorAll('[data-guest-row]').forEach((row, index) => {
                    row.querySelectorAll('[data-guest-field]').forEach((field) => {
                        field.name = `guests[${index}][${field.dataset.guestField}]`;
                    });
                });
            };

            const updateRemoveButtons = () => {
                const rows = guestList?.querySelectorAll('[data-guest-row]') ?? [];
                rows.forEach((row) => {
                    row.querySelector('[data-remove-guest]').disabled = rows.length === 1;
                    row.querySelector('[data-remove-guest]').classList.toggle('opacity-30', rows.length === 1);
                });
            };

            addGuestButton?.addEventListener('click', () => {
                guestList.append(guestTemplate.content.cloneNode(true));
                reindexGuests();
                updateRemoveButtons();
                guestList.lastElementChild.querySelector('[data-guest-field="name"]').focus();
            });

            guestList?.addEventListener('click', (event) => {
                const removeButton = event.target.closest('[data-remove-guest]');

                if (removeButton && guestList.children.length > 1) {
                    removeButton.closest('[data-guest-row]').remove();
                    reindexGuests();
                    updateRemoveButtons();
                }
            });

            updateRemoveButtons();
        })();
    </script>
@endif
