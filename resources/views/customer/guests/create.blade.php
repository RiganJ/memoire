@extends('customer.layouts.app')
@section('title', 'Tambah Tamu')
@section('content')
    <div class="mx-auto max-w-4xl">
        <a href="{{ route('customer.dashboard') }}" class="text-xs text-[#582308]/50"><i class="fa-solid fa-arrow-left mr-2"></i>Kembali ke dashboard</a>
        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div><p class="text-[10px] font-bold uppercase tracking-[.2em] text-[#bd9150]">Daftar penerima</p><h1 class="mt-2 font-display text-4xl text-[#582308] sm:text-5xl">Tambahkan tamu</h1><p class="mt-2 max-w-2xl text-sm leading-6 text-[#32170b]/50">Ketik nama, masukkan ke daftar, lalu simpan setelah semua tamu selesai ditambahkan. Setiap nama otomatis memperoleh link undangan personal.</p></div>
        </div>

        @if ($errors->any())<div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">Daftar belum dapat disimpan. Periksa nama yang kosong, duplikat, atau nomor WhatsApp yang tidak valid.</div>@endif

        <form method="POST" action="{{ route('customer.guests.store') }}" class="mt-7" data-batch-guest-form>
            @csrf
            <section class="rounded-3xl border border-[#582308]/8 bg-white p-5 shadow-[0_16px_45px_rgba(88,35,8,.05)] sm:p-7">
                <div class="grid gap-4 sm:grid-cols-[1.35fr_1fr_auto] sm:items-end">
                    <label class="block text-xs font-semibold text-[#582308]">Nama tamu
                        <input class="mt-2 h-12 w-full rounded-xl border border-[#582308]/10 bg-[#faf7f0] px-4 text-sm outline-none focus:border-[#bd9150] focus:ring-4 focus:ring-[#bd9150]/10" data-draft-name maxlength="180" placeholder="Contoh: Budi & Partner" autocomplete="off" autofocus>
                    </label>
                    <label class="block text-xs font-semibold text-[#582308]">WhatsApp <span class="font-normal text-[#32170b]/35">(opsional)</span>
                        <input class="mt-2 h-12 w-full rounded-xl border border-[#582308]/10 bg-[#faf7f0] px-4 text-sm outline-none focus:border-[#bd9150] focus:ring-4 focus:ring-[#bd9150]/10" data-draft-phone type="tel" inputmode="tel" maxlength="25" placeholder="08123456789" autocomplete="tel">
                    </label>
                    <button type="button" data-add-draft class="inline-flex h-12 items-center justify-center gap-2 rounded-xl bg-[#ead5ac] px-5 text-sm font-semibold text-[#582308] transition hover:bg-[#dfc28b]"><i class="fa-solid fa-plus"></i>Masukkan</button>
                </div>
                <p class="mt-3 hidden text-xs text-red-600" data-draft-error></p>
            </section>

            <section class="mt-5 overflow-hidden rounded-3xl border border-[#582308]/8 bg-white shadow-[0_16px_45px_rgba(88,35,8,.05)]">
                <div class="flex items-center justify-between gap-3 border-b border-[#582308]/8 px-5 py-4 sm:px-7"><div><h2 class="font-display text-2xl text-[#582308]">Tamu siap disimpan</h2><p class="mt-1 text-xs text-[#32170b]/40">Anda masih dapat menghapus nama sebelum menyimpan.</p></div><span class="rounded-full bg-[#582308] px-3 py-1.5 text-xs font-semibold text-white"><span data-guest-count>0</span> tamu</span></div>
                <div class="divide-y divide-[#582308]/8" data-draft-list></div>
                <div class="px-5 py-12 text-center sm:px-7" data-empty-list><i class="fa-regular fa-address-book text-3xl text-[#bd9150]/45"></i><p class="mt-3 text-sm text-[#32170b]/45">Belum ada nama dalam daftar.</p></div>
            </section>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"><a href="{{ route('customer.dashboard') }}" class="grid h-12 place-items-center rounded-xl border border-[#582308]/12 px-6 text-sm font-semibold text-[#582308]">Batal</a><button data-save-guests class="h-12 rounded-xl bg-[#582308] px-7 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-40" disabled>Simpan semua tamu</button></div>
        </form>
    </div>

    <script>
        (() => {
            const form = document.querySelector('[data-batch-guest-form]');
            const nameInput = form.querySelector('[data-draft-name]');
            const phoneInput = form.querySelector('[data-draft-phone]');
            const list = form.querySelector('[data-draft-list]');
            const empty = form.querySelector('[data-empty-list]');
            const count = form.querySelector('[data-guest-count]');
            const saveButton = form.querySelector('[data-save-guests]');
            const error = form.querySelector('[data-draft-error]');
            let guests = @json(old('guests', []));

            const render = () => {
                list.replaceChildren();
                guests.forEach((guest, index) => {
                    const row = document.createElement('div');
                    row.className = 'flex items-center gap-3 px-5 py-4 sm:px-7';
                    row.innerHTML = `<span class="grid size-9 shrink-0 place-items-center rounded-xl bg-[#ead5ac]/35 text-xs font-bold text-[#582308]">${index + 1}</span><div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold text-[#582308]"></p><p class="mt-0.5 text-xs text-[#32170b]/40"></p></div><button type="button" class="grid size-9 shrink-0 place-items-center rounded-xl text-red-500 hover:bg-red-50" aria-label="Hapus tamu"><i class="fa-regular fa-trash-can"></i></button>`;
                    row.querySelector('p:first-child').textContent = guest.name;
                    row.querySelector('p:nth-child(2)').textContent = guest.phone || 'Tanpa nomor WhatsApp';
                    row.querySelector('button').addEventListener('click', () => { guests.splice(index, 1); render(); });
                    ['name', 'phone'].forEach((field) => {
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = `guests[${index}][${field}]`;
                        hidden.value = guest[field] || '';
                        row.appendChild(hidden);
                    });
                    list.appendChild(row);
                });
                count.textContent = guests.length;
                empty.classList.toggle('hidden', guests.length > 0);
                saveButton.disabled = guests.length === 0;
            };

            const addGuest = () => {
                const name = nameInput.value.trim();
                const phone = phoneInput.value.trim();
                error.classList.add('hidden');
                if (!name) { error.textContent = 'Ketik nama tamu terlebih dahulu.'; error.classList.remove('hidden'); nameInput.focus(); return; }
                if (guests.some((guest) => guest.name.toLocaleLowerCase('id') === name.toLocaleLowerCase('id'))) { error.textContent = 'Nama tersebut sudah ada di daftar.'; error.classList.remove('hidden'); nameInput.focus(); return; }
                guests.push({ name, phone });
                nameInput.value = '';
                phoneInput.value = '';
                render();
                nameInput.focus();
            };

            form.querySelector('[data-add-draft]').addEventListener('click', addGuest);
            [nameInput, phoneInput].forEach((input) => input.addEventListener('keydown', (event) => { if (event.key === 'Enter') { event.preventDefault(); addGuest(); } }));
            form.addEventListener('submit', (event) => { if (!guests.length) { event.preventDefault(); } });
            render();
        })();
    </script>
@endsection
