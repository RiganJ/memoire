<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Edit {{ $invitation->name }} — Memoire Admin</title>
        @vite('resources/css/app.css')
    </head>
    <body class="bg-[#f4efe7] text-[#32170b] antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
            @include('admin.partials.sidebar')

            <main class="min-w-0">
                <header class="sticky top-0 z-30 border-b border-[#582308]/10 bg-[#f4efe7]/90 px-5 py-5 backdrop-blur-xl sm:px-8 lg:px-10">
                    <div class="mx-auto flex max-w-6xl items-center justify-between gap-4">
                        <div class="min-w-0">
                            <a href="{{ route('admin.invitations.show', $invitation) }}" class="inline-flex items-center gap-2 text-xs font-medium text-[#582308]/50 transition hover:text-[#582308]">
                                <i class="fa-solid fa-arrow-left text-[10px]"></i>
                                Detail Undangan
                            </a>
                            <h1 class="mt-2 truncate font-display text-2xl text-[#582308] sm:text-3xl">Edit {{ $invitation->name }}</h1>
                        </div>
                        <span class="hidden items-center gap-2 rounded-full border border-[#582308]/10 bg-white px-3 py-2 text-[10px] font-bold uppercase tracking-[.14em] text-[#582308]/55 sm:inline-flex">
                            <span class="size-1.5 rounded-full {{ $invitation->status === 'published' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                            {{ $invitation->status === 'published' ? 'Published' : 'Draft' }}
                        </span>
                    </div>
                </header>

                <form class="mx-auto max-w-6xl p-5 sm:p-8 lg:p-10" method="POST" action="{{ route('admin.invitations.update', $invitation) }}">
                    @csrf
                    @method('PUT')
                    @include('admin.invitations.partials.form', ['submitLabel' => 'Simpan perubahan'])
                </form>
            </main>
        </div>
    </body>
</html>
