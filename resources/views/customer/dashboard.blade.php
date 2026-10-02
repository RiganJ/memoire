@extends('customer.layouts.app')
@section('title', 'Dashboard Customer')
@section('content')
    <section class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-[10px] font-bold uppercase tracking-[.2em] text-[#bd9150]">Ringkasan undangan</p><h1 class="mt-2 font-display text-4xl text-[#582308] sm:text-5xl">Halo, {{ $invitation->name }}</h1><p class="mt-2 text-sm text-[#32170b]/45">Pantau pengiriman, pembukaan, dan jawaban RSVP tamu Anda.</p></div>
        <a href="{{ route('customer.guests.create') }}" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-[#582308] px-5 text-sm font-semibold text-white shadow-lg shadow-[#582308]/15"><i class="fa-solid fa-plus"></i>Tambah tamu</a>
    </section>

    @php($cards = [
        ['sent', 'Undangan dikirim', 'fa-paper-plane', '#582308'],
        ['opened', 'Undangan dibuka', 'fa-envelope-open', '#bd9150'],
        ['attending', 'RSVP hadir', 'fa-circle-check', '#18794e'],
        ['declined', 'Tidak hadir', 'fa-circle-xmark', '#b42318'],
        ['pending', 'Belum menjawab', 'fa-clock', '#6b5c53'],
    ])
    <section class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ($cards as [$key, $label, $icon, $color])
            <article class="rounded-3xl border border-[#582308]/8 bg-white p-5 shadow-[0_12px_35px_rgba(88,35,8,.05)]"><div class="flex items-start justify-between"><span class="grid size-10 place-items-center rounded-xl" style="background: {{ $color }}14; color: {{ $color }}"><i class="fa-solid {{ $icon }}"></i></span><span class="text-[9px] font-bold uppercase tracking-[.14em] text-[#32170b]/30">Total</span></div><p class="mt-5 font-display text-4xl text-[#582308]">{{ number_format($statistics[$key]) }}</p><p class="mt-1 text-xs font-semibold text-[#32170b]/55">{{ $label }}</p></article>
        @endforeach
    </section>

    <section class="mt-8 overflow-hidden rounded-3xl border border-[#582308]/8 bg-white shadow-[0_16px_45px_rgba(88,35,8,.05)]">
        <div class="flex flex-col gap-4 border-b border-[#582308]/8 p-5 sm:p-6 lg:flex-row lg:items-center lg:justify-between"><div><h2 class="font-display text-2xl text-[#582308]">Daftar tamu</h2><p class="mt-1 text-xs text-[#32170b]/40">Setiap tamu memiliki link undangan personal.</p></div><form class="flex flex-col gap-2 sm:flex-row"><input name="search" value="{{ request('search') }}" class="h-10 rounded-xl border border-[#582308]/10 bg-[#faf7f0] px-4 text-xs outline-none focus:border-[#bd9150]" placeholder="Cari nama atau WhatsApp"><select name="status" class="h-10 rounded-xl border border-[#582308]/10 bg-[#faf7f0] px-3 text-xs outline-none"><option value="">Semua RSVP</option><option value="attending" @selected(request('status') === 'attending')>Hadir</option><option value="declined" @selected(request('status') === 'declined')>Tidak hadir</option><option value="pending" @selected(request('status') === 'pending')>Belum menjawab</option></select><button class="h-10 rounded-xl bg-[#ead5ac] px-4 text-xs font-semibold text-[#582308]">Filter</button></form></div>
        <div class="overflow-x-auto"><table class="w-full min-w-[920px] text-left"><thead class="bg-[#faf7f0] text-[9px] uppercase tracking-[.14em] text-[#32170b]/40"><tr><th class="px-6 py-4">Nama tamu</th><th class="px-4 py-4">WhatsApp</th><th class="px-4 py-4">Dibuka</th><th class="px-4 py-4">RSVP</th><th class="px-4 py-4">Link undangan</th><th class="px-6 py-4 text-right">Aksi</th></tr></thead><tbody class="divide-y divide-[#582308]/8">
            @forelse ($guests as $guest)
                @php($guestUrl = route('public.invitations.show', ['invitationSlug' => $invitation->slug, 'guestSlug' => $guest->slug]))
                @php($statusData = match($guest->rsvp_status) { 'attending' => ['Hadir', 'bg-emerald-50 text-emerald-700'], 'declined' => ['Tidak hadir', 'bg-red-50 text-red-700'], default => ['Belum menjawab', 'bg-amber-50 text-amber-700'] })
                <tr><td class="px-6 py-4 text-sm font-semibold text-[#582308]">{{ $guest->name }}</td><td class="px-4 py-4 text-xs text-[#32170b]/55">{{ $guest->phone }}</td><td class="px-4 py-4 text-xs text-[#32170b]/55">{{ $guest->opened_at ? $guest->opened_at->format('d M Y, H:i') : 'Belum' }}@if($guest->open_count)<span class="ml-1 text-[#32170b]/30">({{ $guest->open_count }}x)</span>@endif</td><td class="px-4 py-4"><span class="rounded-full px-2.5 py-1 text-[10px] font-semibold {{ $statusData[1] }}">{{ $statusData[0] }}</span></td><td class="max-w-56 px-4 py-4"><p class="truncate text-[10px] text-[#32170b]/40" title="{{ $guestUrl }}">{{ $guestUrl }}</p></td><td class="px-6 py-4"><div class="flex justify-end gap-1"><button type="button" data-copy-link="{{ $guestUrl }}" class="grid size-8 place-items-center rounded-lg text-[#32170b]/45 hover:bg-[#ead5ac]/35" title="Salin link"><i class="fa-regular fa-copy"></i></button><a href="{{ $guestUrl }}" target="_blank" rel="noopener" class="grid size-8 place-items-center rounded-lg text-[#32170b]/45 hover:bg-[#ead5ac]/35" title="Buka link"><i class="fa-solid fa-arrow-up-right-from-square"></i></a><a href="{{ route('customer.guests.edit', $guest) }}" class="grid size-8 place-items-center rounded-lg text-[#32170b]/45 hover:bg-[#ead5ac]/35" title="Edit"><i class="fa-regular fa-pen-to-square"></i></a><form method="POST" action="{{ route('customer.guests.destroy', $guest) }}" onsubmit="return confirm('Hapus tamu ini?')">@csrf @method('DELETE')<button class="grid size-8 place-items-center rounded-lg text-red-500 hover:bg-red-50" title="Hapus"><i class="fa-regular fa-trash-can"></i></button></form></div></td></tr>
            @empty
                <tr><td colspan="6" class="px-6 py-16 text-center"><i class="fa-regular fa-address-book text-3xl text-[#bd9150]/50"></i><p class="mt-3 text-sm text-[#32170b]/45">Belum ada data tamu yang sesuai.</p></td></tr>
            @endforelse
        </tbody></table></div>
        @if ($guests->hasPages())<div class="border-t border-[#582308]/8 px-6 py-4">{{ $guests->links() }}</div>@endif
    </section>
    <script>
        const copyTextDirectly = async (text) => {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);

                return;
            }

            const temporaryInput = document.createElement('textarea');
            temporaryInput.value = text;
            temporaryInput.setAttribute('readonly', '');
            temporaryInput.style.position = 'fixed';
            temporaryInput.style.top = '0';
            temporaryInput.style.left = '0';
            temporaryInput.style.opacity = '0';
            document.body.appendChild(temporaryInput);
            temporaryInput.focus();
            temporaryInput.select();
            temporaryInput.setSelectionRange(0, temporaryInput.value.length);

            const copied = document.execCommand('copy');
            temporaryInput.remove();

            if (!copied) {
                throw new Error('Browser tidak mengizinkan penyalinan.');
            }
        };

        document.querySelectorAll('[data-copy-link]').forEach((button) => button.addEventListener('click', async () => {
            try {
                await copyTextDirectly(button.dataset.copyLink);
                button.querySelector('i').className = 'fa-solid fa-check';
                setTimeout(() => button.querySelector('i').className = 'fa-regular fa-copy', 1500);
            } catch {
                button.querySelector('i').className = 'fa-solid fa-triangle-exclamation';
            }
        }));
    </script>
@endsection
