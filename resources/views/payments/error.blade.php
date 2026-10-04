<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pembayaran belum tersedia — Memoire</title>
    @include('partials.favicon')
    @vite('resources/css/app.css')
</head>
<body class="grid min-h-screen place-items-center bg-[#f6f1e8] p-5 text-[#32170b]">
    <main class="w-full max-w-lg rounded-3xl border border-[#582308]/10 bg-white p-8 text-center shadow-xl">
        <img src="{{ asset('images/logo-memoire.png') }}" alt="Memoire" class="mx-auto h-20 w-20 object-contain">
        <h1 class="mt-5 font-display text-3xl text-[#582308]">QRIS belum tersedia</h1>
        <p class="mt-3 text-sm leading-7 text-[#32170b]/60">{{ $message }}</p>
        <button type="button" onclick="window.close()" class="mt-7 rounded-full bg-[#582308] px-6 py-3 text-sm font-semibold text-white">Tutup halaman</button>
    </main>
</body>
</html>
