<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Edit Paket — Memoire Admin</title>@vite('resources/css/app.css')</head>
<body class="bg-[#f4efe7] text-[#32170b]"><div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">@include('admin.partials.sidebar')<main>
<header class="border-b border-[#582308]/10 px-5 py-7 sm:px-8 lg:px-10"><a class="text-xs text-[#582308]/55" href="{{ route('admin.packages.index') }}"><i class="fa-solid fa-arrow-left mr-2"></i>Paket & Harga</a><h1 class="mt-4 font-display text-3xl text-[#582308]">Edit {{ $package->name }}</h1></header>
<form class="package-admin-form mx-auto max-w-3xl p-5 sm:p-8 lg:p-10" method="POST" action="{{ route('admin.packages.update',$package) }}">@csrf @method('PUT')
@if($errors->any())<p class="mb-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</p>@endif
@include('admin.packages._form')<button class="mt-7 rounded-full bg-[#582308] px-6 py-3 text-sm font-bold text-white">Simpan perubahan</button></form>
</main></div></body></html>
