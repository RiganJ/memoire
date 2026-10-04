<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $orderForm->exists ? 'Edit' : 'Buat' }} Form — Memoire Admin</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-[#f4efe7] text-[#32170b] antialiased">
@php($isCreating = ! $orderForm->exists)
<div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
    @include('admin.partials.sidebar')
    <main class="min-w-0">
        <header class="border-b border-[#582308]/10 px-5 py-6 sm:px-8 lg:px-10">
            <a href="{{ route('admin.order-forms.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-[#582308]/60 transition hover:text-[#582308]"><i class="fa-solid fa-arrow-left"></i>Semua form</a>
            <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[9px] font-bold uppercase tracking-[.22em] text-[#582308]/45">{{ $isCreating ? 'Formulir baru' : ($orderForm->is_active ? 'Form checkout aktif' : 'Form nonaktif') }}</p>
                    <h1 class="mt-2 font-display text-3xl text-[#582308] sm:text-4xl">{{ $isCreating ? 'Rancang form pesanan' : 'Edit form pesanan' }}</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-[#32170b]/50">{{ $isCreating ? 'Susun pertanyaan yang akan diminta dari pelanggan. Form baru tidak langsung mengubah checkout.' : 'Perbarui identitas form dan susunan pertanyaan checkout.' }}</p>
                </div>
                @unless($isCreating)
                    <span class="inline-flex w-fit items-center gap-2 rounded-full {{ $orderForm->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-stone-100 text-stone-600' }} px-3.5 py-2 text-xs font-bold"><span class="size-1.5 rounded-full {{ $orderForm->is_active ? 'bg-emerald-500' : 'bg-stone-400' }}"></span>{{ $orderForm->is_active ? 'Sedang digunakan' : 'Nonaktif' }}</span>
                @endunless
            </div>
        </header>

        <form method="POST" action="{{ $isCreating ? route('admin.order-forms.store') : route('admin.order-forms.update', $orderForm) }}" class="mx-auto max-w-5xl space-y-6 p-5 sm:p-8 lg:p-10">
            @csrf
            @unless($isCreating)
                @method('PUT')
            @endunless
            @if(session('success'))
                <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800"><i class="fa-solid fa-circle-check"></i>{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800"><i class="fa-solid fa-circle-exclamation mt-0.5"></i><span>{{ $errors->first() }}</span></div>
            @endif

            @include('admin.order-forms._form')

            <div class="sticky bottom-4 z-20 flex flex-col-reverse gap-3 rounded-2xl border border-[#582308]/10 bg-[#fffdfa]/95 p-3 shadow-[0_12px_35px_rgba(50,23,11,.12)] backdrop-blur sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ $isCreating ? route('admin.order-forms.index') : route('admin.order-forms.show', $orderForm) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl px-5 text-sm font-semibold text-[#582308]/65 transition hover:bg-[#582308]/5">Batalkan</a>
                <button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#582308] px-6 text-sm font-bold text-white shadow-[0_8px_20px_rgba(88,35,8,.16)] transition hover:bg-[#713719]"><i class="fa-solid fa-check text-xs"></i>{{ $isCreating ? 'Simpan form' : 'Simpan perubahan' }}</button>
            </div>
        </form>
    </main>
</div>
</body>
</html>
