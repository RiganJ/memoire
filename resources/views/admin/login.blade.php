<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Login Admin Memoire">
        <title>Login Admin — Memoire</title>
        @include('partials.favicon')
        @vite('resources/css/app.css')
    </head>
    <body class="min-h-screen bg-[#f4efe7] text-[#32170b] antialiased">
        <main class="grid min-h-screen lg:grid-cols-[1.05fr_.95fr]">
            <section class="relative hidden overflow-hidden bg-[#582308] p-12 text-[#fffaf0] lg:flex lg:flex-col lg:justify-between xl:p-16">
                <div class="absolute -left-32 -top-32 size-[30rem] rounded-full border border-[#ead5ac]/15"></div>
                <div class="absolute -left-20 -top-20 size-[22rem] rounded-full border border-[#ead5ac]/10"></div>
                <div class="absolute -bottom-48 -right-32 size-[38rem] rounded-full border border-[#ead5ac]/15"></div>
                <div class="absolute -bottom-36 -right-20 size-[28rem] rounded-full border border-[#ead5ac]/10"></div>

                <a href="{{ url('/') }}" class="relative z-10 flex w-fit items-center gap-4">
                    <img src="{{ asset('images/logo-memoire.png') }}" alt="Logo Memoire" class="h-20 w-20 object-contain">
                    <div class="border-l border-[#bd9150]/50 pl-4">
                        <p class="font-display text-xl tracking-[.22em] text-[#ead5ac]">MEMOIRE</p>
                        <p class="mt-1 text-[8px] uppercase tracking-[.24em] text-white/40">A home for moments</p>
                    </div>
                </a>

                <div class="relative z-10 max-w-xl">
                    <p class="flex items-center gap-3 text-[10px] font-bold uppercase tracking-[.32em] text-[#ead5ac]"><span class="h-px w-9 bg-[#bd9150]"></span>Memoire Studio</p>
                    <h1 class="mt-7 font-display text-5xl leading-[1.03] xl:text-6xl">Kelola setiap cerita<br><em class="font-normal text-[#ead5ac]">dengan sepenuh hati.</em></h1>
                    <p class="mt-7 max-w-lg text-sm leading-7 text-white/50">Satu ruang untuk memantau pesanan, memperbarui koleksi, dan memastikan setiap momen pelanggan tersampaikan dengan sempurna.</p>
                </div>

                <div class="relative z-10 flex items-center justify-between border-t border-white/10 pt-6 text-[10px] text-white/35">
                    <p>© {{ date('Y') }} Memoire Studio</p>
                    <p>Admin Workspace</p>
                </div>
            </section>

            <section class="relative flex min-h-screen items-start justify-center overflow-hidden px-0 pb-8 pt-0 sm:px-0 lg:items-center lg:px-16 lg:py-12">
                <div class="absolute inset-x-0 top-0 h-[20rem] bg-[#582308] lg:hidden">
                    <a href="{{ url('/') }}" class="absolute left-5 top-5 flex items-center gap-2 text-xs font-medium text-white/65 transition hover:text-white"><i class="fa-solid fa-arrow-left text-[10px]"></i>Kembali ke website</a>
                    <div class="absolute left-6 top-[4.5rem] flex items-center gap-3">
                        <img src="{{ asset('images/logo-memoire.png') }}" alt="" class="h-14 w-14 object-contain">
                        <div class="border-l border-[#bd9150]/50 pl-3">
                            <p class="font-display text-base tracking-[.2em] text-[#ead5ac]">MEMOIRE</p>
                            <p class="mt-1 text-[8px] uppercase tracking-[.2em] text-white/45">A home for moments</p>
                        </div>
                    </div>
                    <div class="absolute inset-x-6 bottom-24">
                        <p class="text-[9px] font-bold uppercase tracking-[.28em] text-[#ead5ac]">Admin workspace</p>
                        <p class="mt-2 font-display text-3xl leading-tight text-white">Ruang kerja,<br><em class="font-normal text-[#ead5ac]">penuh cerita.</em></p>
                    </div>
                    <span aria-hidden="true" class="absolute -right-1 bottom-3 font-display text-[8rem] leading-none text-white/[.035]">M</span>
                </div>

                <div class="relative mt-[14rem] w-full max-w-md rounded-2xl border border-[#582308]/[.08] bg-[#fffaf0] px-6 py-7 shadow-[0_18px_45px_rgba(50,23,11,.12)] sm:px-8 lg:mt-0 lg:rounded-none lg:border-0 lg:bg-transparent lg:px-0 lg:py-0 lg:shadow-none">

                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[.28em] text-[#bd9150]">Admin area</p>
                        <h2 class="mt-3 font-display text-4xl text-[#582308] sm:mt-4 sm:text-5xl">Selamat datang.</h2>
                        <p class="mt-3 text-sm leading-6 text-[#32170b]/50">Masuk menggunakan akun admin untuk melanjutkan ke dashboard.</p>
                    </div>

                    <form action="{{ route('admin.login.store') }}" method="POST" class="mt-9 space-y-5">
                        @csrf

                        @if ($errors->any())
                            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs leading-5 text-red-700" role="alert">
                                {{ $errors->first() }}
                            </div>
                        @endif
                        <div>
                            <label for="email" class="mb-2 block text-xs font-semibold text-[#32170b]/65">Alamat email</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 grid w-12 place-items-center text-[#bd9150]"><i class="fa-regular fa-envelope text-sm"></i></span>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required maxlength="254" placeholder="admin@memoire.id" class="h-14 w-full rounded-xl border bg-white pl-12 pr-4 text-sm text-[#32170b] outline-none transition placeholder:text-[#32170b]/25 focus:border-[#bd9150] focus:ring-4 focus:ring-[#bd9150]/10 {{ $errors->has('email') ? 'border-red-300' : 'border-[#582308]/12' }}">
                            </div>
                        </div>

                        <div>
                            <div class="mb-2 flex items-center justify-between"><label for="password" class="text-xs font-semibold text-[#32170b]/65">Kata sandi</label><span class="hidden text-[11px] text-[#32170b]/40 sm:inline">Hubungi pengelola akun jika lupa</span></div>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 grid w-12 place-items-center text-[#bd9150]"><i class="fa-solid fa-lock text-sm"></i></span>
                                <input id="password" name="password" type="password" autocomplete="current-password" required minlength="8" maxlength="128" placeholder="Masukkan kata sandi" class="h-14 w-full rounded-xl border bg-white pl-12 pr-4 text-sm text-[#32170b] outline-none transition placeholder:text-[#32170b]/25 focus:border-[#bd9150] focus:ring-4 focus:ring-[#bd9150]/10 {{ $errors->has('password') ? 'border-red-300' : 'border-[#582308]/12' }}">
                            </div>
                            <p class="mt-2 text-[10px] leading-5 text-[#32170b]/40">Minimal 8 karakter dengan huruf besar, huruf kecil, angka, dan simbol.</p>
                        </div>

                        <div>
                            <div class="mb-2 flex items-center justify-between"><label for="captcha" class="text-xs font-semibold text-[#32170b]/65">Kode verifikasi</label><a href="{{ route('admin.login') }}" class="text-[11px] font-semibold text-[#582308] transition hover:text-[#bd9150]"><i class="fa-solid fa-rotate-right mr-1 text-[9px]"></i>Muat ulang kode</a></div>
                            <div class="grid gap-3 sm:grid-cols-[1.35fr_1fr]">
                                <div class="flex h-14 items-center justify-center overflow-hidden rounded-xl border border-[#582308]/12 bg-[#f8ecd4] [&>svg]:h-full [&>svg]:w-full">{!! $captchaSvg !!}</div>
                                <input id="captcha" name="captcha" type="text" inputmode="text" autocomplete="off" autocapitalize="characters" required minlength="5" maxlength="5" placeholder="Ketik kode" class="h-14 w-full rounded-xl border bg-white px-4 text-center text-sm font-semibold uppercase tracking-[.18em] text-[#32170b] outline-none transition placeholder:font-normal placeholder:tracking-normal placeholder:text-[#32170b]/25 focus:border-[#bd9150] focus:ring-4 focus:ring-[#bd9150]/10 {{ $errors->has('captcha') ? 'border-red-300' : 'border-[#582308]/12' }}">
                            </div>
                            <p class="mt-2 text-[10px] leading-5 text-[#32170b]/40">Masukkan lima karakter yang terlihat pada gambar.</p>
                        </div>

                        <label class="flex w-fit cursor-pointer items-center gap-3 text-xs text-[#32170b]/55"><input type="checkbox" name="remember" value="1" class="size-4 rounded border-[#582308]/20 accent-[#582308]">Ingat saya di perangkat ini</label>

                        <button type="submit" class="flex h-14 w-full items-center justify-center gap-3 rounded-xl bg-[#582308] text-sm font-semibold text-white shadow-[0_14px_35px_rgba(88,35,8,.18)] transition hover:-translate-y-0.5 hover:bg-[#713719]">
                            Masuk ke Dashboard <i class="fa-solid fa-arrow-right text-xs"></i>
                        </button>
                    </form>

                    <div class="mt-8 flex items-center gap-4"><span class="h-px flex-1 bg-[#582308]/10"></span><p class="text-[9px] uppercase tracking-[.2em] text-[#32170b]/30">Secure workspace</p><span class="h-px flex-1 bg-[#582308]/10"></span></div>
                    <p class="mt-6 text-center text-xs leading-6 text-[#32170b]/40"><i class="fa-solid fa-shield-halved mr-2 text-[#bd9150]"></i>Akses ini hanya ditujukan untuk administrator Memoire.</p>
                </div>
            </section>
        </main>
    </body>
</html>
