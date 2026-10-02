<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Edit {{ $catalog->name }} — Memoire Admin</title>
        @vite('resources/css/app.css')
    </head>
    <body class="bg-[#f4efe7] text-[#32170b] antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
            @include('admin.partials.sidebar')
            <main class="min-w-0">
                <header class="sticky top-0 z-30 flex h-20 items-center border-b border-[#582308]/10 bg-[#f4efe7]/90 px-5 backdrop-blur-xl sm:px-8 lg:h-24 lg:px-10">
                    <div><a href="{{ route('admin.catalog.index') }}" class="text-xs text-[#32170b]/45"><i class="fa-solid fa-arrow-left mr-2"></i>Kembali ke katalog</a><h1 class="mt-2 font-display text-2xl text-[#582308] sm:text-3xl">Edit Desain</h1></div>
                </header>
                <div class="p-5 sm:p-8 lg:p-10">
                    <form action="{{ route('admin.catalog.update', $catalog) }}" method="POST" enctype="multipart/form-data" class="mx-auto max-w-4xl rounded-3xl border border-[#582308]/8 bg-white p-5 shadow-[0_10px_35px_rgba(88,35,8,.04)] sm:p-8">
                        @csrf
                        @method('PUT')
                        @include('admin.catalog.partials.form', ['submitLabel' => 'Perbarui Desain'])
                    </form>
                </div>
            </main>
        </div>
    </body>
</html>
