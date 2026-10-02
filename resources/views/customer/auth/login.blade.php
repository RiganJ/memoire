<!DOCTYPE html>
<html lang="id">
    <head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Login Customer — Memoire</title>@vite('resources/css/app.css')</head>
    <body class="min-h-screen bg-[#32170b] text-[#fffaf3] antialiased">
        <main class="relative grid min-h-screen place-items-center overflow-hidden p-5">
            <div class="pointer-events-none absolute -left-24 -top-24 size-96 rounded-full border border-[#ead5ac]/10"></div><div class="pointer-events-none absolute -bottom-44 -right-32 size-[34rem] rounded-full border border-[#ead5ac]/10"></div>
            <section class="relative w-full max-w-md overflow-hidden rounded-[2rem] border border-white/10 bg-[#4a200d] shadow-2xl shadow-black/30">
                <div class="border-b border-white/10 px-7 py-8 text-center sm:px-10"><span class="mx-auto grid size-14 place-items-center rounded-2xl bg-[#ead5ac] text-xl text-[#582308]"><i class="fa-regular fa-heart"></i></span><p class="mt-5 text-[9px] font-bold uppercase tracking-[.3em] text-[#ead5ac]/55">Memoire Customer Portal</p><h1 class="mt-3 font-display text-4xl text-[#ead5ac]">Selamat datang</h1><p class="mx-auto mt-3 max-w-xs text-xs leading-6 text-white/50">Masukkan kode unik yang diberikan admin untuk mengelola tamu undangan Anda.</p></div>
                <form method="POST" action="{{ route('customer.login.store') }}" class="p-7 sm:p-10">@csrf
                    @if (session('success'))<div class="mb-5 rounded-xl border border-emerald-300/20 bg-emerald-400/10 p-3 text-xs text-emerald-200">{{ session('success') }}</div>@endif
                    <div>
                        <label for="access_code" class="block text-[10px] font-bold uppercase tracking-[.16em] text-[#ead5ac]/65">Kode akses</label>
                        <div class="relative mt-2">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-12 items-center justify-center text-sm text-[#ead5ac]/45" aria-hidden="true">
                                <i class="fa-solid fa-key"></i>
                            </span>
                            <input id="access_code" name="access_code" value="{{ old('access_code') }}" maxlength="20" autocomplete="one-time-code" autofocus required aria-describedby="access-code-error" class="h-13 w-full rounded-xl border border-white/10 bg-white/8 py-3 pl-12 pr-4 font-mono text-sm uppercase tracking-[.12em] text-white outline-none transition placeholder:text-white/20 focus:border-[#ead5ac]/60 focus:ring-4 focus:ring-[#ead5ac]/10 sm:tracking-[.18em]" placeholder="MEMOIRE-XXXXXXXX">
                        </div>
                        @error('access_code')<span id="access-code-error" class="mt-2 block text-xs text-red-300">{{ $message }}</span>@enderror
                    </div>
                    <button class="mt-6 flex h-13 w-full items-center justify-center gap-2 rounded-xl bg-[#ead5ac] text-sm font-bold text-[#582308] transition hover:bg-[#f2dfb9]" type="submit">Masuk ke dashboard <i class="fa-solid fa-arrow-right text-xs"></i></button>
                    <p class="mt-5 text-center text-[10px] leading-5 text-white/35">Tidak memiliki kode? Hubungi admin Memoire.</p>
                </form>
            </section>
        </main>
    </body>
</html>
