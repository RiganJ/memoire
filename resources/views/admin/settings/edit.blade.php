<!DOCTYPE html>
<html lang="id">
    <head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Pengaturan — Memoire Admin</title>@vite('resources/css/app.css')</head>
    <body class="bg-[#f4efe7] text-[#32170b] antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
            @include('admin.partials.sidebar')
            <main class="min-w-0">
                <header class="sticky top-0 z-30 flex h-20 items-center border-b border-[#582308]/10 bg-[#f4efe7]/90 px-5 backdrop-blur-xl sm:px-8 lg:h-24 lg:px-10"><div><p class="text-xs text-[#32170b]/45">Konfigurasi brand</p><h1 class="mt-1 font-display text-2xl text-[#582308] sm:text-3xl">Pengaturan</h1></div></header>
                <div class="mx-auto max-w-4xl p-5 sm:p-8 lg:p-10">
                    @if (session('success'))<div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800"><i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}</div>@endif
                    @if ($errors->any())<div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
                    <form action="{{ route('admin.settings.update') }}" method="POST" class="rounded-3xl border border-[#582308]/8 bg-white p-6 shadow-[0_10px_35px_rgba(88,35,8,.04)] sm:p-8">@csrf @method('PUT')
                        <div class="flex items-start gap-4 border-b border-[#582308]/8 pb-6"><span class="grid size-11 place-items-center rounded-xl bg-[#ead5ac]/45 text-[#582308]"><i class="fa-solid fa-sliders"></i></span><div><h2 class="font-display text-2xl text-[#582308]">Identitas & kontak</h2><p class="mt-1 text-xs leading-6 text-[#32170b]/45">Perubahan di sini langsung dipakai oleh landing page dan live chat.</p></div></div>
                        <div class="mt-7 grid gap-5 sm:grid-cols-2"><label class="text-xs font-semibold text-[#32170b]/60">Nama brand<input name="brand_name" value="{{ old('brand_name',$settings['brand_name']) }}" required class="mt-2 h-11 w-full rounded-xl border border-[#582308]/10 bg-[#faf7f0] px-4 text-sm outline-none focus:border-[#bd9150]"></label><label class="text-xs font-semibold text-[#32170b]/60">Tagline<input name="brand_tagline" value="{{ old('brand_tagline',$settings['brand_tagline']) }}" required class="mt-2 h-11 w-full rounded-xl border border-[#582308]/10 bg-[#faf7f0] px-4 text-sm outline-none focus:border-[#bd9150]"></label><label class="text-xs font-semibold text-[#32170b]/60">Nomor WhatsApp konsultasi<input name="whatsapp_number" inputmode="numeric" value="{{ old('whatsapp_number',$settings['whatsapp_number']) }}" required placeholder="6281372339347" class="mt-2 h-11 w-full rounded-xl border border-[#582308]/10 bg-[#faf7f0] px-4 text-sm outline-none focus:border-[#bd9150]"><span class="mt-2 block text-[10px] font-normal text-[#32170b]/40">Gunakan format angka dengan kode negara, tanpa + atau spasi.</span></label><label class="text-xs font-semibold text-[#32170b]/60">URL Instagram<input name="instagram_url" type="url" value="{{ old('instagram_url',$settings['instagram_url']) }}" required class="mt-2 h-11 w-full rounded-xl border border-[#582308]/10 bg-[#faf7f0] px-4 text-sm outline-none focus:border-[#bd9150]"></label><label class="text-xs font-semibold text-[#32170b]/60 sm:col-span-2">Sapaan awal live chat<textarea name="chat_greeting" required maxlength="250" class="mt-2 min-h-24 w-full rounded-xl border border-[#582308]/10 bg-[#faf7f0] px-4 py-3 text-sm outline-none focus:border-[#bd9150]">{{ old('chat_greeting',$settings['chat_greeting']) }}</textarea></label></div>
                        <div class="mt-8 flex justify-end border-t border-[#582308]/8 pt-6"><button class="h-11 rounded-xl bg-[#582308] px-6 text-sm font-semibold text-white"><i class="fa-solid fa-floppy-disk mr-2"></i>Simpan Pengaturan</button></div>
                    </form>
                </div>
            </main>
        </div>
    </body>
</html>
