@php
    $navigation = [
        ['Dashboard', 'fa-table-columns', route('admin.dashboard'), 'admin.dashboard'],
        ['Pesanan', 'fa-envelope-open-text', route('admin.orders.index'), 'admin.orders.*'],
        ['Template Undangan', 'fa-layer-group', route('admin.templates.index'), 'admin.templates.*'],
        ['Undangan', 'fa-envelope-open-text', route('admin.invitations.index'), 'admin.invitations.*'],
        ['Katalog', 'fa-layer-group', route('admin.catalog.index'), 'admin.catalog.*'],
        ['Pelanggan', 'fa-users', route('admin.customers.index'), 'admin.customers.*'],
        ['Pembayaran', 'fa-wallet', route('admin.payments.index'), 'admin.payments.*'],
        ['Paket & Harga', 'fa-tags', route('admin.packages.index'), 'admin.packages.*'],
        ['Live Chat', 'fa-comments', route('admin.chats.index'), 'admin.chats.*'],
        ['Form Pesanan', 'fa-clipboard-list', route('admin.order-forms.index'), 'admin.order-forms.*'],
    ];
    $activeChatCount = \App\Models\Conversation::active()->count();
@endphp

<button id="admin-mobile-sidebar-toggle" type="button" aria-label="Buka navigasi admin" aria-controls="admin-mobile-sidebar" aria-expanded="false" class="fixed left-5 top-5 z-40 grid size-11 place-items-center rounded-xl border border-[#582308]/15 bg-white text-[#582308] shadow-[0_4px_14px_rgba(50,23,11,.12)] transition hover:-translate-y-px hover:bg-[#fffaf0] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#bd9150] lg:hidden">
    <i class="fa-solid fa-bars" aria-hidden="true"></i>
</button>

<dialog id="admin-mobile-sidebar" class="admin-mobile-sidebar fixed inset-0 z-50 m-0 h-dvh max-h-none w-screen max-w-none overflow-hidden border-0 bg-transparent p-0 text-[#fffaf0] lg:hidden">
    <aside class="admin-mobile-sidebar-panel flex h-full w-[min(19rem,86vw)] flex-col bg-[#582308] shadow-[18px_0_50px_rgba(20,7,2,.24)]">
        <div class="flex h-20 shrink-0 items-center gap-3 px-5">
            <img src="{{ asset('images/logo-memoire.png') }}" alt="" class="h-12 w-12 object-contain">
            <div class="border-l border-[#bd9150]/50 pl-3">
                <p class="font-display text-base tracking-[.18em] text-[#ead5ac]">MEMOIRE</p>
                <p class="mt-1 text-[8px] uppercase tracking-[.2em] text-white/40">Admin Studio</p>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto border-t border-white/10 px-4 py-5" aria-label="Navigasi admin mobile">
            <div class="grid gap-1">
                @foreach ($navigation as [$label, $icon, $href, $routePattern])
                    @php($active = $routePattern && request()->routeIs($routePattern))
                    <a href="{{ $href }}" @if ($active) aria-current="page" @endif class="flex min-h-12 items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition {{ $active ? 'bg-[#ead5ac] text-[#582308]' : 'text-white/75 hover:bg-white/8 hover:text-white' }}">
                        <span class="grid size-8 shrink-0 place-items-center rounded-lg {{ $active ? 'bg-white/45' : 'bg-white/7' }}"><i class="fa-solid {{ $icon }} text-xs"></i></span>
                        <span>{{ $label }}</span>
                        @if ($label === 'Live Chat' && $activeChatCount > 0)
                            <span class="ml-auto rounded-full bg-[#bd9150] px-2 py-0.5 text-[9px] font-bold text-[#582308]">{{ $activeChatCount }}</span>
                        @endif
                    </a>
                @endforeach
                <a href="{{ route('admin.settings.edit') }}" @if (request()->routeIs('admin.settings.*')) aria-current="page" @endif class="flex min-h-12 items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition {{ request()->routeIs('admin.settings.*') ? 'bg-[#ead5ac] text-[#582308]' : 'text-white/75 hover:bg-white/8 hover:text-white' }}">
                    <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-white/7"><i class="fa-solid fa-gear text-xs"></i></span>
                    Pengaturan
                </a>
                <a href="{{ url('/') }}" class="flex min-h-12 items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-white/75 transition hover:bg-white/8 hover:text-white">
                    <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-white/7"><i class="fa-solid fa-arrow-up-right-from-square text-xs"></i></span>
                    Lihat website
                </a>
            </div>
            <form action="{{ route('admin.logout') }}" method="POST" class="mt-3 border-t border-white/10 pt-3">
                @csrf
                <button type="submit" class="flex min-h-12 w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-white/75 transition hover:bg-white/8 hover:text-white">
                    <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-white/7"><i class="fa-solid fa-arrow-right-from-bracket text-xs"></i></span>
                    Keluar
                </button>
            </form>
        </nav>

        <form action="{{ route('admin.logout') }}" method="POST" class="shrink-0 border-t border-white/10 p-4">
            @csrf
            <button type="submit" class="flex min-h-12 w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-white/75 transition hover:bg-white/8 hover:text-white">
                <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-white/7"><i class="fa-solid fa-arrow-right-from-bracket text-xs"></i></span>
                Keluar
            </button>
        </form>
    </aside>
</dialog>

<script>
    (() => {
        const dialog = document.getElementById('admin-mobile-sidebar');
        const toggle = document.getElementById('admin-mobile-sidebar-toggle');

        if (!dialog || !toggle) {
            return;
        }

        toggle.addEventListener('click', () => {
            dialog.showModal();
            toggle.setAttribute('aria-expanded', 'true');
        });

        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });

        dialog.addEventListener('close', () => {
            toggle.setAttribute('aria-expanded', 'false');
        });
    })();
</script>

<aside class="hidden min-h-screen flex-col bg-[#582308] text-[#fffaf0] lg:flex">
    <div class="flex h-24 items-center gap-3 border-b border-white/10 px-7">
        <img src="{{ asset('images/logo-memoire.png') }}" alt="Logo Memoire" class="h-16 w-16 object-contain">
        <div class="border-l border-[#bd9150]/50 pl-3">
            <p class="font-display text-lg tracking-[.18em] text-[#ead5ac]">MEMOIRE</p>
            <p class="mt-1 text-[8px] uppercase tracking-[.2em] text-white/40">Admin Studio</p>
        </div>
    </div>

    <nav class="flex flex-1 flex-col gap-1 px-4 py-7" aria-label="Navigasi admin">
        <p class="mb-3 px-3 text-[9px] font-bold uppercase tracking-[.25em] text-white/35">Workspace</p>

        @foreach ($navigation as [$label, $icon, $href, $routePattern])
            @php($active = $routePattern && request()->routeIs($routePattern))
            <a
                href="{{ $href }}"
                @if ($active) aria-current="page" @endif
                class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm transition {{ $active ? 'bg-[#ead5ac] text-[#582308]' : 'text-white/65 hover:bg-white/8 hover:text-white' }}"
            >
                <span class="grid size-8 place-items-center rounded-lg {{ $active ? 'bg-white/45' : 'bg-white/7' }}">
                    <i class="fa-solid {{ $icon }} text-xs"></i>
                </span>
                <span>{{ $label }}</span>
                @if ($label === 'Live Chat' && $activeChatCount > 0)
                    <span class="ml-auto rounded-full bg-[#bd9150] px-2 py-0.5 text-[9px] font-bold text-[#582308]">{{ $activeChatCount }}</span>
                @endif
            </a>
        @endforeach

        <p class="mb-3 mt-7 px-3 text-[9px] font-bold uppercase tracking-[.25em] text-white/35">Pengaturan</p>
        <a href="{{ route('admin.settings.edit') }}" @if (request()->routeIs('admin.settings.*')) aria-current="page" @endif class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm transition {{ request()->routeIs('admin.settings.*') ? 'bg-[#ead5ac] text-[#582308]' : 'text-white/65 hover:bg-white/8 hover:text-white' }}">
            <span class="grid size-8 place-items-center rounded-lg {{ request()->routeIs('admin.settings.*') ? 'bg-white/45' : 'bg-white/7' }}"><i class="fa-solid fa-gear text-xs"></i></span>
            Pengaturan
        </a>
        <a href="{{ url('/') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm text-white/65 transition hover:bg-white/8 hover:text-white">
            <span class="grid size-8 place-items-center rounded-lg bg-white/7"><i class="fa-solid fa-arrow-up-right-from-square text-xs"></i></span>
            Lihat website
        </a>
        <form action="{{ route('admin.logout') }}" method="POST">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm text-white/65 transition hover:bg-white/8 hover:text-white">
                <span class="grid size-8 place-items-center rounded-lg bg-white/7"><i class="fa-solid fa-arrow-right-from-bracket text-xs"></i></span>
                Keluar
            </button>
        </form>
    </nav>

    <div class="m-4 rounded-2xl border border-white/10 bg-white/5 p-4">
        <div class="flex items-center gap-3">
            <span class="grid size-10 place-items-center rounded-full bg-[#ead5ac] font-display font-bold text-[#582308]">MA</span>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold">Memoire Admin</p>
                <p class="truncate text-xs text-white/40">{{ auth()->user()->email }}</p>
            </div>
        </div>
    </div>
</aside>
