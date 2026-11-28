@extends('customer.layouts.app')
@section('title', 'Dashboard Customer')
@section('content')
    <section class="overflow-hidden rounded-[2rem] bg-[#582308] text-white shadow-[0_20px_55px_rgba(88,35,8,.16)]">
        <div class="relative flex flex-col gap-7 p-6 sm:p-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="pointer-events-none absolute -right-20 -top-28 size-80 rounded-full border border-white/10"></div><div class="pointer-events-none absolute -right-2 top-8 size-40 rounded-full border border-white/10"></div>
            <div class="relative"><p class="text-[10px] font-bold uppercase tracking-[.22em] text-[#ead5ac]/70">Dashboard undangan</p><h1 class="mt-3 max-w-4xl font-display text-4xl leading-tight text-[#fff7e9] sm:text-5xl">Halo, {{ $invitation->name }}</h1><p class="mt-3 max-w-2xl text-sm leading-6 text-white/55">Kelola daftar penerima, bagikan link personal, serta pantau RSVP dan ucapan tamu dalam satu tempat.</p></div>
            <a href="{{ route('customer.guests.create') }}" class="relative inline-flex h-12 shrink-0 items-center justify-center gap-2 rounded-full bg-[#ead5ac] px-6 text-sm font-bold text-[#582308] shadow-lg shadow-black/10"><i class="fa-solid fa-user-plus"></i>Tambah beberapa tamu</a>
        </div>
    </section>

    @php($cards = [
        ['sent', 'Undangan dikirim', 'fa-paper-plane', '#582308'],
        ['opened', 'Undangan dibuka', 'fa-envelope-open', '#bd9150'],
        ['attending', 'RSVP hadir', 'fa-circle-check', '#18794e'],
        ['declined', 'Tidak hadir', 'fa-circle-xmark', '#b42318'],
        ['pending', 'Belum menjawab', 'fa-clock', '#6b5c53'],
        ['wishes', 'Ucapan masuk', 'fa-comment-dots', '#8b4b8f'],
    ])
    <section class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        @foreach ($cards as [$key, $label, $icon, $color])
            <article class="rounded-3xl border border-[#582308]/8 bg-white p-5 shadow-[0_12px_35px_rgba(88,35,8,.05)]"><div class="flex items-start justify-between"><span class="grid size-10 place-items-center rounded-xl" style="background: {{ $color }}14; color: {{ $color }}"><i class="fa-solid {{ $icon }}"></i></span><span class="text-[9px] font-bold uppercase tracking-[.14em] text-[#32170b]/30">Total</span></div><p class="mt-5 font-display text-4xl text-[#582308]">{{ number_format($statistics[$key]) }}</p><p class="mt-1 text-xs font-semibold text-[#32170b]/55">{{ $label }}</p></article>
        @endforeach
    </section>

    <section class="mt-8 overflow-hidden rounded-3xl border border-[#582308]/8 bg-white shadow-[0_16px_45px_rgba(88,35,8,.05)]">
        <div class="flex flex-col gap-4 border-b border-[#582308]/8 p-5 sm:p-6 lg:flex-row lg:items-center lg:justify-between"><div><h2 class="font-display text-2xl text-[#582308]">Daftar tamu</h2><p class="mt-1 text-xs text-[#32170b]/40">Pilih tamu untuk mengirim undangan personal melalui WhatsApp.</p></div><div class="flex flex-col gap-2 sm:flex-row"><button id="send-bulk-whatsapp" type="button" disabled class="flex h-10 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-xs font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-40"><i class="fa-brands fa-whatsapp text-sm"></i><span>Kirim WhatsApp</span></button><form class="flex flex-col gap-2 sm:flex-row"><input name="search" value="{{ request('search') }}" class="h-10 rounded-xl border border-[#582308]/10 bg-[#faf7f0] px-4 text-xs outline-none focus:border-[#bd9150]" placeholder="Cari nama atau WhatsApp"><select name="status" class="h-10 rounded-xl border border-[#582308]/10 bg-[#faf7f0] px-3 text-xs outline-none"><option value="">Semua RSVP</option><option value="attending" @selected(request('status') === 'attending')>Hadir</option><option value="declined" @selected(request('status') === 'declined')>Tidak hadir</option><option value="pending" @selected(request('status') === 'pending')>Belum menjawab</option></select><button class="h-10 rounded-xl bg-[#ead5ac] px-4 text-xs font-semibold text-[#582308]">Filter</button></form></div></div>
        <div class="overflow-x-auto"><table class="w-full min-w-[980px] text-left"><thead class="bg-[#faf7f0] text-[9px] uppercase tracking-[.14em] text-[#32170b]/40"><tr><th class="px-4 py-4"><input id="select-all-guests" type="checkbox" class="size-4 accent-emerald-600" aria-label="Pilih semua tamu yang memiliki nomor WhatsApp di halaman ini"></th><th class="px-4 py-4">Nama tamu</th><th class="px-4 py-4">WhatsApp</th><th class="px-4 py-4">Dibuka</th><th class="px-4 py-4">RSVP</th><th class="px-4 py-4">Link undangan</th><th class="px-6 py-4 text-right">Aksi</th></tr></thead><tbody class="divide-y divide-[#582308]/8">
            @forelse ($guests as $guest)
                @php($guestUrl = route('public.invitations.show', ['invitationSlug' => $invitation->slug, 'guestSlug' => $guest->slug]))
                @php($whatsappNumber = preg_replace('/\D+/', '', $guest->phone ?? ''))
                @php($whatsappNumber = str_starts_with($whatsappNumber, '0') ? '62'.substr($whatsappNumber, 1) : (str_starts_with($whatsappNumber, '8') ? '62'.$whatsappNumber : $whatsappNumber))
                @php($whatsappText = rawurlencode("Kepada Yth.\n{$guest->name}\n\nDengan penuh kebahagiaan kami mengundang Anda untuk menghadiri pernikahan kami.\n\nSilakan membuka undangan personal melalui:\n{$guestUrl}"))
                @php($whatsappUrl = $whatsappNumber !== '' ? "https://wa.me/{$whatsappNumber}?text={$whatsappText}" : null)
                @php($statusData = match($guest->rsvp_status) { 'attending' => ['Hadir', 'bg-emerald-50 text-emerald-700'], 'declined' => ['Tidak hadir', 'bg-red-50 text-red-700'], default => ['Belum menjawab', 'bg-amber-50 text-amber-700'] })
                <tr><td class="px-4 py-4"><input type="checkbox" class="guest-whatsapp-checkbox size-4 accent-emerald-600 disabled:cursor-not-allowed disabled:opacity-30" @disabled($whatsappUrl === null) @if($whatsappUrl) data-whatsapp-url="{{ $whatsappUrl }}" @endif aria-label="{{ $whatsappUrl ? 'Pilih '.$guest->name : $guest->name.' belum memiliki nomor WhatsApp' }}" title="{{ $whatsappUrl ? 'Pilih untuk dikirim melalui WhatsApp' : 'Tambahkan nomor WhatsApp terlebih dahulu' }}"></td><td class="px-4 py-4"><a href="{{ $guestUrl }}" target="_blank" rel="noopener" class="group inline-flex items-center gap-2 text-sm font-semibold text-[#582308]" title="Buka undangan personal {{ $guest->name }}"><span>{{ $guest->name }}</span><i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-[#bd9150] opacity-0 transition group-hover:opacity-100"></i></a></td><td class="px-4 py-4 text-xs text-[#32170b]/55">{{ $guest->phone ?: '—' }}</td><td class="px-4 py-4 text-xs text-[#32170b]/55">{{ $guest->opened_at ? $guest->opened_at->format('d M Y, H:i') : 'Belum' }}@if($guest->open_count)<span class="ml-1 text-[#32170b]/30">({{ $guest->open_count }}x)</span>@endif</td><td class="px-4 py-4"><span class="rounded-full px-2.5 py-1 text-[10px] font-semibold {{ $statusData[1] }}">{{ $statusData[0] }}</span></td><td class="max-w-56 px-4 py-4"><p class="truncate text-[10px] text-[#32170b]/40" title="{{ $guestUrl }}">{{ $guestUrl }}</p></td><td class="px-6 py-4"><div class="flex justify-end gap-1"><button type="button" data-copy-link="{{ $guestUrl }}" class="grid size-8 place-items-center rounded-lg text-[#32170b]/45 hover:bg-[#ead5ac]/35" title="Salin link"><i class="fa-regular fa-copy"></i></button><a href="{{ $guestUrl }}" target="_blank" rel="noopener" class="grid size-8 place-items-center rounded-lg text-[#32170b]/45 hover:bg-[#ead5ac]/35" title="Buka link"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>@if($whatsappUrl)<a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="grid size-8 place-items-center rounded-lg text-emerald-600 hover:bg-emerald-50" title="Kirim undangan ke WhatsApp {{ $guest->name }}" aria-label="Kirim undangan ke WhatsApp {{ $guest->name }}"><i class="fa-brands fa-whatsapp"></i></a>@endif<a href="{{ route('customer.guests.edit', $guest) }}" class="grid size-8 place-items-center rounded-lg text-[#32170b]/45 hover:bg-[#ead5ac]/35" title="Edit"><i class="fa-regular fa-pen-to-square"></i></a><form method="POST" action="{{ route('customer.guests.destroy', $guest) }}" onsubmit="return confirm('Hapus tamu ini?')">@csrf @method('DELETE')<button class="grid size-8 place-items-center rounded-lg text-red-500 hover:bg-red-50" title="Hapus"><i class="fa-regular fa-trash-can"></i></button></form></div></td></tr>
            @empty
                <tr><td colspan="7" class="px-6 py-16 text-center"><i class="fa-regular fa-address-book text-3xl text-[#bd9150]/50"></i><p class="mt-3 text-sm text-[#32170b]/45">Belum ada data tamu yang sesuai.</p></td></tr>
            @endforelse
        </tbody></table></div>
        @if ($guests->hasPages())<div class="border-t border-[#582308]/8 px-6 py-4">{{ $guests->links() }}</div>@endif
    </section>

    <section class="mt-8 grid gap-6 lg:grid-cols-2">
        <article class="overflow-hidden rounded-3xl border border-[#582308]/8 bg-white shadow-[0_16px_45px_rgba(88,35,8,.05)]">
            <header class="flex items-center justify-between gap-4 border-b border-[#582308]/8 p-5 sm:p-6"><div><p class="text-[9px] font-bold uppercase tracking-[.18em] text-emerald-700">Konfirmasi kehadiran</p><h2 class="mt-1 font-display text-2xl text-[#582308]">Tamu yang akan hadir</h2></div><span class="grid size-11 place-items-center rounded-2xl bg-emerald-50 text-emerald-700"><i class="fa-solid fa-user-check"></i></span></header>
            <div class="divide-y divide-[#582308]/8">
                @forelse ($attendees as $guest)
                    <div class="flex items-center gap-3 px-5 py-4 sm:px-6"><span class="grid size-9 shrink-0 place-items-center rounded-full bg-emerald-50 text-xs font-bold text-emerald-700">{{ str($guest->name)->substr(0, 1)->upper() }}</span><div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold text-[#582308]">{{ $guest->name }}</p><p class="mt-0.5 text-[11px] text-[#32170b]/40">{{ $guest->party_size ? $guest->party_size.' orang' : 'Hadir' }} · {{ $guest->rsvp_responded_at?->format('d M Y, H:i') }}</p></div><i class="fa-solid fa-circle-check text-emerald-600"></i></div>
                @empty
                    <div class="px-6 py-12 text-center"><i class="fa-regular fa-calendar-check text-3xl text-emerald-700/25"></i><p class="mt-3 text-sm text-[#32170b]/45">Belum ada tamu yang menandai hadir.</p></div>
                @endforelse
            </div>
            @if ($attendees->hasPages())<div class="border-t border-[#582308]/8 px-6 py-4">{{ $attendees->links() }}</div>@endif
        </article>

        <article class="overflow-hidden rounded-3xl border border-[#582308]/8 bg-white shadow-[0_16px_45px_rgba(88,35,8,.05)]">
            <header class="flex items-center justify-between gap-4 border-b border-[#582308]/8 p-5 sm:p-6"><div><p class="text-[9px] font-bold uppercase tracking-[.18em] text-[#8b4b8f]">Guestbook</p><h2 class="mt-1 font-display text-2xl text-[#582308]">Semua ucapan tamu</h2></div><span class="grid size-11 place-items-center rounded-2xl bg-purple-50 text-[#8b4b8f]"><i class="fa-solid fa-comments"></i></span></header>
            <div class="divide-y divide-[#582308]/8">
                @forelse ($wishes as $guest)
                    <blockquote class="px-5 py-4 sm:px-6"><div class="flex items-start justify-between gap-4"><p class="text-sm font-semibold text-[#582308]">{{ $guest->name }}</p><time class="shrink-0 text-[10px] text-[#32170b]/35">{{ $guest->wish_submitted_at?->format('d M Y') }}</time></div><p class="mt-2 text-sm italic leading-6 text-[#32170b]/60">“{{ $guest->wish_message }}”</p></blockquote>
                @empty
                    <div class="px-6 py-12 text-center"><i class="fa-regular fa-comment-dots text-3xl text-[#8b4b8f]/25"></i><p class="mt-3 text-sm text-[#32170b]/45">Belum ada ucapan yang masuk.</p></div>
                @endforelse
            </div>
            @if ($wishes->hasPages())<div class="border-t border-[#582308]/8 px-6 py-4">{{ $wishes->links() }}</div>@endif
        </article>
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

        const guestWhatsAppCheckboxes = [...document.querySelectorAll('.guest-whatsapp-checkbox:not(:disabled)')];
        const selectAllGuests = document.getElementById('select-all-guests');
        const sendBulkWhatsApp = document.getElementById('send-bulk-whatsapp');
        const updateBulkWhatsAppButton = () => {
            const selectedCount = guestWhatsAppCheckboxes.filter((checkbox) => checkbox.checked).length;
            sendBulkWhatsApp.disabled = selectedCount === 0;
            sendBulkWhatsApp.querySelector('span').textContent = selectedCount > 0 ? `Kirim WhatsApp (${selectedCount})` : 'Kirim WhatsApp';
            selectAllGuests.disabled = guestWhatsAppCheckboxes.length === 0;
            selectAllGuests.checked = guestWhatsAppCheckboxes.length > 0 && selectedCount === guestWhatsAppCheckboxes.length;
            selectAllGuests.indeterminate = selectedCount > 0 && selectedCount < guestWhatsAppCheckboxes.length;
        };

        selectAllGuests?.addEventListener('change', () => {
            guestWhatsAppCheckboxes.forEach((checkbox) => { checkbox.checked = selectAllGuests.checked; });
            updateBulkWhatsAppButton();
        });
        guestWhatsAppCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', updateBulkWhatsAppButton));
        sendBulkWhatsApp?.addEventListener('click', () => {
            const selectedUrls = guestWhatsAppCheckboxes
                .filter((checkbox) => checkbox.checked)
                .map((checkbox) => checkbox.dataset.whatsappUrl);
            const openedWindows = selectedUrls.map((url) => {
                const openedWindow = window.open('about:blank', '_blank');

                if (openedWindow) {
                    openedWindow.opener = null;
                    openedWindow.location.href = url;
                }

                return openedWindow;
            });

            if (openedWindows.some((openedWindow) => openedWindow === null)) {
                window.alert('Sebagian WhatsApp tidak dapat dibuka. Izinkan pop-up untuk situs ini, lalu klik Kirim WhatsApp lagi.');
            }
        });

        updateBulkWhatsAppButton();
    </script>
@endsection
