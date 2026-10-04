@php($initialFields = old('fields', $orderForm->fields ?? \App\Models\OrderFormTemplate::BASIC_FIELDS))
<div class="space-y-6">
    <section class="rounded-3xl border border-[#582308]/10 bg-white p-5 shadow-[0_8px_28px_rgba(88,35,8,.04)] sm:p-7">
        <div class="flex items-center gap-4">
            <span class="grid size-11 place-items-center rounded-2xl bg-[#f4e8d3] text-[#582308]"><i class="fa-regular fa-pen-to-square"></i></span>
            <div>
                <p class="text-[9px] font-bold uppercase tracking-[.2em] text-[#582308]/45">Identitas</p>
                <h2 class="mt-1 font-display text-2xl text-[#582308]">Kenali form ini</h2>
            </div>
        </div>
        <div class="mt-6 grid gap-5 sm:grid-cols-2">
            <label class="text-xs font-bold text-[#582308] sm:col-span-2">Nama form
                <input required maxlength="120" name="name" value="{{ old('name', $orderForm->name) }}" class="mt-2 block min-h-12 w-full rounded-xl border border-[#582308]/15 bg-[#fffdfa] px-4 text-sm font-medium text-[#32170b] outline-none transition placeholder:text-[#32170b]/30 focus:border-[#bd9150] focus:ring-4 focus:ring-[#bd9150]/10" placeholder="Contoh: Form Undangan Pernikahan">
                @error('name')<span class="mt-1 block text-xs font-medium text-red-600">{{ $message }}</span>@enderror
            </label>
            <label class="text-xs font-bold text-[#582308] sm:col-span-2">Deskripsi <span class="font-normal text-[#32170b]/40">· opsional</span>
                <textarea name="description" maxlength="1000" rows="3" class="mt-2 block w-full rounded-xl border border-[#582308]/15 bg-[#fffdfa] px-4 py-3 text-sm font-medium leading-6 text-[#32170b] outline-none transition placeholder:text-[#32170b]/30 focus:border-[#bd9150] focus:ring-4 focus:ring-[#bd9150]/10" placeholder="Jelaskan informasi yang diminta dari pelanggan.">{{ old('description', $orderForm->description) }}</textarea>
                @error('description')<span class="mt-1 block text-xs font-medium text-red-600">{{ $message }}</span>@enderror
            </label>
        </div>
    </section>

    <section class="rounded-3xl border border-[#582308]/10 bg-white p-5 shadow-[0_8px_28px_rgba(88,35,8,.04)] sm:p-7" data-form-builder>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <span class="grid size-11 place-items-center rounded-2xl bg-[#f4e8d3] text-[#582308]"><i class="fa-solid fa-list-check"></i></span>
                <div>
                    <p class="text-[9px] font-bold uppercase tracking-[.2em] text-[#582308]/45">Isi form</p>
                    <h2 class="mt-1 font-display text-2xl text-[#582308]">Pertanyaan</h2>
                    <p class="mt-1 text-xs leading-5 text-[#32170b]/50">Urutkan pertanyaan sesuai alur yang nyaman untuk pelanggan. Identitas teknis dibuat otomatis oleh sistem.</p>
                </div>
            </div>
            <button type="button" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#ead5ac] px-4 text-xs font-bold text-[#582308] transition hover:bg-[#f0dfbd]" data-add-field><i class="fa-solid fa-plus"></i> Tambah pertanyaan</button>
        </div>
        <div class="mt-6 space-y-4" data-field-list></div>
        <p class="mt-5 hidden rounded-2xl border border-dashed border-[#582308]/20 bg-[#faf7f0] px-4 py-8 text-center text-sm text-[#32170b]/45" data-empty-fields>Belum ada pertanyaan. Tambahkan pertanyaan untuk melanjutkan.</p>
    </section>

    <section class="overflow-hidden rounded-3xl border border-[#582308]/10 bg-[#faf7f0]">
        <div class="flex flex-col gap-3 border-b border-[#582308]/10 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
            <div class="flex items-center gap-3">
                <span class="grid size-10 place-items-center rounded-xl bg-white text-[#582308] shadow-sm"><i class="fa-regular fa-credit-card"></i></span>
                <div><h2 class="font-display text-xl text-[#582308]">Metode pembayaran</h2><p class="mt-1 text-[11px] text-[#32170b]/50">Tersedia setelah pelanggan mengirim form pesanan.</p></div>
            </div>
            <a href="{{ route('admin.payment-methods.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-[#582308]/15 px-4 text-xs font-bold text-[#582308] transition hover:bg-white">Kelola metode</a>
        </div>
        <div class="grid gap-3 p-4 sm:grid-cols-2 sm:p-5">
            @forelse($paymentMethods as $paymentMethod)
                <div class="flex items-center gap-3 rounded-2xl border border-[#582308]/8 bg-white p-4">
                    <span class="grid size-10 place-items-center rounded-xl bg-[#f4e8d3] text-[#582308]"><i class="fa-solid {{ $paymentMethod->code === 'dana' ? 'fa-qrcode' : 'fa-building-columns' }}"></i></span>
                    <div><p class="text-sm font-bold text-[#582308]">{{ $paymentMethod->name }}</p><p class="mt-1 text-xs text-[#32170b]/45">{{ $paymentMethod->account_number }}</p></div>
                    <span class="ml-auto size-2 rounded-full bg-emerald-500" title="Aktif"></span>
                </div>
            @empty
                <p class="rounded-2xl border border-dashed border-[#582308]/15 bg-white p-5 text-sm text-[#32170b]/50 sm:col-span-2">Belum ada metode pembayaran aktif.</p>
            @endforelse
        </div>
    </section>
</div>

<template data-field-template>
    <article class="rounded-2xl border border-[#582308]/10 bg-[#fffdfa] p-4 shadow-[0_3px_12px_rgba(88,35,8,.035)] transition sm:p-5" data-field-row>
        <input type="hidden" data-field-key>
        <div class="flex items-center justify-between gap-3 border-b border-[#582308]/8 pb-3">
            <div class="flex min-w-0 items-center gap-3"><span class="grid size-9 shrink-0 place-items-center rounded-xl bg-[#f4e8d3] text-[10px] font-bold text-[#582308]" data-field-number></span><strong class="truncate text-sm text-[#582308]" data-field-title>Pertanyaan baru</strong></div>
            <div class="flex shrink-0 gap-1">
                <button type="button" class="grid size-9 place-items-center rounded-xl text-[#582308]/45 transition hover:bg-[#f4e8d3] disabled:opacity-30" data-move-up aria-label="Geser ke atas"><i class="fa-solid fa-arrow-up text-xs"></i></button>
                <button type="button" class="grid size-9 place-items-center rounded-xl text-[#582308]/45 transition hover:bg-[#f4e8d3] disabled:opacity-30" data-move-down aria-label="Geser ke bawah"><i class="fa-solid fa-arrow-down text-xs"></i></button>
                <button type="button" class="grid size-9 place-items-center rounded-xl text-red-500 transition hover:bg-red-50" data-remove-field aria-label="Hapus pertanyaan"><i class="fa-regular fa-trash-can text-xs"></i></button>
            </div>
        </div>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <label class="text-xs font-bold text-[#582308]">Label pertanyaan<input required maxlength="120" data-field-label class="mt-2 block min-h-11 w-full rounded-xl border border-[#582308]/15 bg-white px-3.5 text-sm font-medium outline-none transition focus:border-[#bd9150] focus:ring-4 focus:ring-[#bd9150]/10" placeholder="Contoh: Lokasi acara"></label>
            <label class="text-xs font-bold text-[#582308]">Tipe jawaban<select required data-field-type class="mt-2 block min-h-11 w-full rounded-xl border border-[#582308]/15 bg-white px-3.5 text-sm font-medium outline-none transition focus:border-[#bd9150] focus:ring-4 focus:ring-[#bd9150]/10"><option value="text">Jawaban singkat</option><option value="textarea">Jawaban panjang</option><option value="date">Tanggal</option><option value="select">Pilihan</option></select></label>
            <label class="flex min-h-11 items-center gap-3 rounded-xl border border-[#582308]/10 bg-white px-3.5 text-xs font-semibold text-[#582308] sm:col-span-2"><input type="checkbox" value="1" data-field-required class="size-4 rounded border-[#582308]/20 text-[#582308] focus:ring-[#bd9150]"> Wajib diisi</label>
            <label class="hidden text-xs font-bold text-[#582308] sm:col-span-2" data-options-wrap>Daftar pilihan<textarea rows="3" maxlength="2000" data-field-options class="mt-2 block w-full rounded-xl border border-[#582308]/15 bg-white px-3.5 py-3 text-sm font-medium outline-none transition focus:border-[#bd9150] focus:ring-4 focus:ring-[#bd9150]/10" placeholder="Satu pilihan per baris"></textarea><span class="mt-1 block text-[10px] font-normal text-[#32170b]/45">Masukkan minimal satu pilihan.</span></label>
        </div>
    </article>
</template>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const builder = document.querySelector('[data-form-builder]');
    if (!builder) return;

    const list = builder.querySelector('[data-field-list]');
    const template = document.querySelector('[data-field-template]');
    const empty = builder.querySelector('[data-empty-fields]');
    const initialFields = {{ Illuminate\Support\Js::from($initialFields) }};

    const refresh = () => {
        const rows = Array.from(list.querySelectorAll('[data-field-row]'));
        rows.forEach((row, index) => {
            row.querySelector('[data-field-number]').textContent = String(index + 1).padStart(2, '0');
            row.querySelector('[data-field-key]').name = `fields[${index}][key]`;
            row.querySelector('[data-field-label]').name = `fields[${index}][label]`;
            row.querySelector('[data-field-type]').name = `fields[${index}][type]`;
            row.querySelector('[data-field-required]').name = `fields[${index}][required]`;
            row.querySelector('[data-field-options]').name = `fields[${index}][options]`;
            row.querySelector('[data-move-up]').disabled = index === 0;
            row.querySelector('[data-move-down]').disabled = index === rows.length - 1;
        });
        empty.classList.toggle('hidden', rows.length > 0);
    };

    const addField = (field = {}) => {
        const row = template.content.firstElementChild.cloneNode(true);
        const key = row.querySelector('[data-field-key]');
        const label = row.querySelector('[data-field-label]');
        const type = row.querySelector('[data-field-type]');
        const required = row.querySelector('[data-field-required]');
        const options = row.querySelector('[data-field-options]');
        const optionsWrap = row.querySelector('[data-options-wrap]');

        key.value = field.key || '';
        label.value = field.label || '';
        type.value = field.type || 'text';
        required.checked = Boolean(field.required);
        options.value = Array.isArray(field.options) ? field.options.join('\n') : (field.options || '');

        const syncType = () => {
            const isSelect = type.value === 'select';
            optionsWrap.classList.toggle('hidden', !isSelect);
            options.required = isSelect;
        };
        const syncTitle = () => {
            row.querySelector('[data-field-title]').textContent = label.value.trim() || 'Pertanyaan baru';
        };

        label.addEventListener('input', syncTitle);
        type.addEventListener('change', syncType);
        row.querySelector('[data-remove-field]').addEventListener('click', () => {
            row.remove();
            refresh();
        });
        row.querySelector('[data-move-up]').addEventListener('click', () => {
            if (row.previousElementSibling) list.insertBefore(row, row.previousElementSibling);
            refresh();
        });
        row.querySelector('[data-move-down]').addEventListener('click', () => {
            if (row.nextElementSibling) list.insertBefore(row.nextElementSibling, row);
            refresh();
        });

        syncTitle();
        syncType();
        list.append(row);
        refresh();
    };

    builder.querySelector('[data-add-field]').addEventListener('click', () => {
        if (list.querySelectorAll('[data-field-row]').length < 30) {
            addField();
        }
    });
    initialFields.forEach(addField);
    refresh();
});
</script>
