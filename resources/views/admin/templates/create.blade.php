<!DOCTYPE html>
<html lang="id">
    <head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Tambah Template — Memoire Admin</title>@vite('resources/css/app.css')</head>
    <body class="bg-[#f4efe7] text-[#32170b] antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
            @include('admin.partials.sidebar')
            <main class="min-w-0">
                <header class="sticky top-0 z-30 flex h-20 items-center border-b border-[#582308]/10 bg-[#f4efe7]/90 px-5 backdrop-blur-xl sm:px-8 lg:h-24 lg:px-10"><div><a href="{{ route('admin.templates.index') }}" class="text-xs text-[#582308]/50 transition hover:text-[#582308]"><i class="fa-solid fa-arrow-left mr-2"></i>Template Undangan</a><h1 class="mt-1 font-display text-2xl text-[#582308] sm:text-3xl">Template Baru</h1></div></header>
                <form class="mx-auto max-w-6xl p-5 sm:p-8 lg:p-10" method="POST" action="{{ route('admin.templates.store') }}" enctype="multipart/form-data">
                    @csrf
                    @include('admin.templates.partials.form', ['template' => null, 'submitLabel' => 'Simpan template'])
                </form>
            </main>
        </div>
    </body>
</html>
