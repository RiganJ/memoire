@php($input = 'mt-2 h-11 w-full rounded-xl border border-[#582308]/10 bg-[#faf7f0] px-4 text-sm outline-none focus:border-[#bd9150]')
@if($errors->any())<div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">Periksa kembali data tamu di bawah.</div>@endif
<div class="rounded-3xl border border-[#582308]/8 bg-white p-6 shadow-sm sm:p-8">
    <div class="space-y-5">
        <label class="block text-xs font-semibold text-[#582308]">
            Nama Tamu *
            <input class="{{ $input }}" name="name" value="{{ old('name', $guest?->name) }}" maxlength="180" placeholder="Budi & Partner" required>
            @error('name')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror
        </label>
        <label class="block text-xs font-semibold text-[#582308]">
            Nomor WhatsApp *
            <input class="{{ $input }}" type="tel" inputmode="tel" name="phone" value="{{ old('phone', $guest?->phone) }}" maxlength="25" placeholder="08123456789" required>
            @error('phone')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror
        </label>
        <p class="rounded-xl bg-[#faf7f0] px-4 py-3 text-[11px] leading-5 text-[#32170b]/50">Link undangan personal dibuat otomatis dari nama tamu setelah data disimpan.</p>
    </div>
</div>
<div class="mt-6 flex justify-end gap-3"><a href="{{ route('admin.invitations.show', $invitation) }}" class="grid h-11 place-items-center rounded-xl border border-[#582308]/12 px-5 text-sm font-semibold text-[#582308]">Batal</a><button class="h-11 rounded-xl bg-[#582308] px-6 text-sm font-semibold text-white">{{ $submitLabel }}</button></div>
