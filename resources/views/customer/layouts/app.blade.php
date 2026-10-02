<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Portal Customer') — Memoire</title>
        @vite('resources/css/app.css')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body class="min-h-screen bg-[#f6f1e8] text-[#32170b] antialiased">
        <header class="sticky top-0 z-30 border-b border-[#582308]/10 bg-[#fffaf3]/90 backdrop-blur-xl">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-4 sm:px-8">
                <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-3">
                    <span class="grid size-10 place-items-center rounded-2xl bg-[#582308] text-sm text-[#ead5ac]"><i class="fa-regular fa-heart"></i></span>
                    <span><b class="block font-display text-xl leading-none text-[#582308]">Memoire</b><small class="text-[9px] uppercase tracking-[.18em] text-[#32170b]/40">Customer Portal</small></span>
                </a>
                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block"><p class="text-xs font-semibold text-[#582308]">{{ $invitation->name }}</p><p class="mt-0.5 text-[10px] text-[#32170b]/40">Kode {{ $invitation->customer_access_code }}</p></div>
                    <form method="POST" action="{{ route('customer.logout') }}" class="js-logout-form">@csrf<button class="grid size-10 place-items-center rounded-xl border border-[#582308]/10 bg-white text-[#582308] transition hover:bg-[#582308] hover:text-white" aria-label="Keluar" title="Keluar"><i class="fa-solid fa-arrow-right-from-bracket"></i></button></form>
                </div>
            </div>
        </header>
        <main class="mx-auto max-w-7xl px-5 py-7 sm:px-8 sm:py-10">
            @if (session('success'))
                <div class="mb-6 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800" role="status"><i class="fa-solid fa-circle-check"></i>{{ session('success') }}</div>
            @endif
            @yield('content')
        </main>
        <script>
            document.querySelectorAll('.js-logout-form').forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    const result = await Swal.fire({
                        title: 'Keluar dari portal?',
                        text: 'Anda perlu memasukkan kode akses lagi untuk kembali.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, keluar',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#582308',
                        cancelButtonColor: '#a8a29e',
                        reverseButtons: true,
                    });

                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        </script>
    </body>
</html>
