<!DOCTYPE html>
<html lang="id">
    <head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $invitation->name }} — Memoire Admin</title>@vite('resources/css/app.css')</head>
    <body class="bg-[#f4efe7] text-[#32170b] antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
            @include('admin.partials.sidebar')
            <main class="min-w-0">
                <header class="border-b border-[#582308]/10 px-5 py-7 sm:px-8 lg:px-10"><div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><a href="{{ route('admin.invitations.index') }}" class="text-xs text-[#582308]/50"><i class="fa-solid fa-arrow-left mr-2"></i>Daftar Undangan</a><h1 class="mt-3 font-display text-3xl text-[#582308]">{{ $invitation->name }}</h1><p class="mt-1 font-mono text-[10px] text-[#32170b]/40">/{{ $invitation->slug }}</p></div><div class="flex gap-2"><a href="{{ route('public.invitations.show', ['invitationSlug' => $invitation->slug]) }}" target="_blank" rel="noopener" class="grid h-10 place-items-center rounded-xl border border-[#582308]/12 bg-white px-4 text-xs font-semibold text-[#582308]"><i class="fa-regular fa-eye mr-2"></i>Preview</a><a href="{{ route('admin.invitations.edit', $invitation) }}" class="grid h-10 place-items-center rounded-xl bg-[#582308] px-4 text-xs font-semibold text-white"><i class="fa-regular fa-pen-to-square mr-2"></i>Edit</a></div></div></header>
                <div class="space-y-6 p-5 sm:p-8 lg:p-10">
                    @if(session('success'))<div role="status" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>@endif
                    <nav class="flex gap-1 overflow-x-auto rounded-2xl border border-[#582308]/8 bg-white p-1.5 text-xs font-semibold"><a href="#informasi" class="shrink-0 rounded-xl bg-[#582308] px-4 py-2.5 text-white">Informasi</a><a href="#template" class="shrink-0 rounded-xl px-4 py-2.5 text-[#582308] hover:bg-[#f7f0e5]">Template</a><a href="#data-tamu" class="shrink-0 rounded-xl px-4 py-2.5 text-[#582308] hover:bg-[#f7f0e5]">Data Tamu</a><a href="{{ route('public.invitations.show', ['invitationSlug' => $invitation->slug]) }}" target="_blank" rel="noopener" class="shrink-0 rounded-xl px-4 py-2.5 text-[#582308] hover:bg-[#f7f0e5]">Preview <i class="fa-solid fa-arrow-up-right-from-square ml-1"></i></a></nav>

                    <div class="grid gap-6 xl:grid-cols-[1fr_20rem]">
                        <section id="informasi" class="rounded-3xl border border-[#582308]/8 bg-white p-6 shadow-sm sm:p-7"><p class="text-[9px] font-bold uppercase tracking-[.18em] text-[#bd9150]">Informasi</p><dl class="mt-5 grid gap-5 sm:grid-cols-2">@foreach([['Nama undangan',$invitation->name],['Pengantin pria',$invitation->groom_name],['Pengantin wanita',$invitation->bride_name],['Status',ucfirst($invitation->status)]] as [$label,$value])<div class="rounded-2xl bg-[#faf7f0] p-4"><dt class="text-[9px] uppercase tracking-[.12em] text-[#32170b]/35">{{ $label }}</dt><dd class="mt-2 text-sm font-semibold text-[#582308]">{{ $value }}</dd></div>@endforeach</dl></section>
                        <aside id="template" class="rounded-3xl bg-[#582308] p-6 text-white shadow-sm"><p class="text-[9px] font-bold uppercase tracking-[.18em] text-[#ead5ac]/65">Template</p><h2 class="mt-3 font-display text-2xl text-[#ead5ac]">{{ $invitation->template->name }}</h2><p class="mt-2 font-mono text-[10px] text-white/40">invitations/{{ $invitation->template->folder_name }}</p><p class="mt-5 border-t border-white/10 pt-4 text-xs leading-6 text-white/55">Template ini digunakan bersama. Perubahan file akan berlaku ke semua undangan yang memakainya.</p><a href="{{ route('admin.templates.edit', $invitation->template) }}" class="mt-5 inline-flex items-center gap-2 text-xs font-semibold text-[#ead5ac]">Kelola template <i class="fa-solid fa-arrow-right"></i></a></aside>
                    </div>

                    <section id="data-tamu" class="overflow-hidden rounded-3xl border border-[#582308]/8 bg-white shadow-sm">
                        <div class="flex flex-col gap-4 border-b border-[#582308]/8 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6"><div><h2 class="font-display text-2xl text-[#582308]">Data Tamu</h2><p class="mt-1 text-xs text-[#32170b]/40">{{ $invitation->guests_count }} penerima undangan</p></div><div class="flex flex-wrap gap-2"><button id="send-bulk-whatsapp" type="button" disabled class="flex h-10 items-center gap-2 rounded-full bg-emerald-600 px-4 text-xs font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-40"><i class="fa-brands fa-whatsapp"></i><span>Kirim Massal</span></button><a href="{{ route('admin.invitation-guests.create', $invitation) }}" class="flex h-10 w-fit items-center gap-2 rounded-full bg-[#582308] px-4 text-xs font-semibold text-white"><i class="fa-solid fa-plus"></i>Tambah Tamu</a></div></div>
                        <div class="overflow-x-auto"><table class="w-full min-w-[820px] text-left"><thead class="bg-[#faf7f0] text-[9px] uppercase tracking-[.14em] text-[#32170b]/40"><tr><th class="px-4 py-4"><input id="select-all-guests" type="checkbox" class="size-4 accent-[#582308]" aria-label="Pilih semua tamu di halaman ini"></th><th class="px-4 py-4">Nama Tamu</th><th class="px-4 py-4">WhatsApp</th><th class="px-4 py-4">Link Undangan</th><th class="px-6 py-4 text-right">Action</th></tr></thead><tbody class="divide-y divide-[#582308]/8">
                            @forelse($guests as $guest)
                                @php($guestUrl = route('public.invitations.show', ['invitationSlug' => $invitation->slug, 'guestSlug' => $guest->slug]))
                                @php($whatsappNumber = preg_replace('/\D+/', '', $guest->phone))
                                @php($whatsappNumber = str_starts_with($whatsappNumber, '0') ? '62'.substr($whatsappNumber, 1) : (str_starts_with($whatsappNumber, '8') ? '62'.$whatsappNumber : $whatsappNumber))
                                @php($whatsappText = rawurlencode("Kepada Yth.\n{$guest->name}\n\nDengan penuh kebahagiaan kami mengundang Anda untuk menghadiri pernikahan kami.\n\nSilakan membuka undangan melalui:\n{$guestUrl}"))
                                <tr><td class="px-4 py-4"><input type="checkbox" class="guest-whatsapp-checkbox size-4 accent-[#582308]" data-whatsapp-url="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappText }}" aria-label="Pilih {{ $guest->name }}"></td><td class="px-4 py-4 text-sm font-semibold text-[#582308]">{{ $guest->name }}</td><td class="px-4 py-4 text-xs text-[#32170b]/55">{{ $guest->phone }}</td><td class="max-w-64 px-4 py-4"><p class="truncate text-[10px] text-[#32170b]/40" title="{{ $guestUrl }}">{{ $guestUrl }}</p></td><td class="px-6 py-4"><div class="flex justify-end gap-1"><button type="button" class="copy-invitation-link grid size-8 place-items-center rounded-lg text-[#32170b]/45 hover:bg-[#ead5ac]/35 hover:text-[#582308]" data-link="{{ $guestUrl }}" aria-label="Copy link {{ $guest->name }}" title="Copy Link"><i class="fa-regular fa-copy"></i></button><a href="{{ $guestUrl }}" target="_blank" rel="noopener" class="grid size-8 place-items-center rounded-lg text-[#32170b]/45 hover:bg-[#ead5ac]/35 hover:text-[#582308]" aria-label="Open {{ $guest->name }}" title="Open"><i class="fa-solid fa-arrow-up-right-from-square"></i></a><a href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappText }}" target="_blank" rel="noopener" class="grid size-8 place-items-center rounded-lg text-emerald-600 hover:bg-emerald-50" aria-label="Share WhatsApp {{ $guest->name }}" title="Share WhatsApp"><i class="fa-brands fa-whatsapp"></i></a><a href="{{ route('admin.invitation-guests.edit', [$invitation, $guest]) }}" class="grid size-8 place-items-center rounded-lg text-[#32170b]/45 hover:bg-[#ead5ac]/35 hover:text-[#582308]" aria-label="Edit {{ $guest->name }}"><i class="fa-regular fa-pen-to-square"></i></a><form method="POST" action="{{ route('admin.invitation-guests.destroy', [$invitation, $guest]) }}" onsubmit="return confirm('Hapus tamu ini?')">@csrf @method('DELETE')<button class="grid size-8 place-items-center rounded-lg text-[#32170b]/45 hover:bg-red-50 hover:text-red-600" aria-label="Hapus {{ $guest->name }}"><i class="fa-regular fa-trash-can"></i></button></form></div></td></tr>
                            @empty<tr><td colspan="5" class="px-6 py-14 text-center text-sm text-[#32170b]/45">Belum ada data tamu. Tambahkan penerima undangan pertama.</td></tr>@endforelse
                        </tbody></table></div>@if($guests->hasPages())<div class="border-t border-[#582308]/8 px-6 py-4">{{ $guests->links() }}</div>@endif
                    </section>
                </div>
            </main>
        </div>
        <script>
            document.querySelectorAll('.copy-invitation-link').forEach((button) => button.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(button.dataset.link);
                    const icon = button.querySelector('i');
                    icon.className = 'fa-solid fa-check';
                    setTimeout(() => { icon.className = 'fa-regular fa-copy'; }, 1500);
                } catch {
                    window.prompt('Salin link undangan:', button.dataset.link);
                }
            }));

            const guestCheckboxes = [...document.querySelectorAll('.guest-whatsapp-checkbox')];
            const selectAllGuests = document.getElementById('select-all-guests');
            const sendBulkWhatsApp = document.getElementById('send-bulk-whatsapp');
            const updateBulkButton = () => {
                const selectedCount = guestCheckboxes.filter((checkbox) => checkbox.checked).length;
                sendBulkWhatsApp.disabled = selectedCount === 0;
                sendBulkWhatsApp.querySelector('span').textContent = selectedCount ? `Kirim Massal (${selectedCount})` : 'Kirim Massal';
                selectAllGuests.checked = guestCheckboxes.length > 0 && selectedCount === guestCheckboxes.length;
                selectAllGuests.indeterminate = selectedCount > 0 && selectedCount < guestCheckboxes.length;
            };

            selectAllGuests?.addEventListener('change', () => {
                guestCheckboxes.forEach((checkbox) => { checkbox.checked = selectAllGuests.checked; });
                updateBulkButton();
            });
            guestCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', updateBulkButton));
            sendBulkWhatsApp?.addEventListener('click', () => {
                const selectedUrls = guestCheckboxes.filter((checkbox) => checkbox.checked).map((checkbox) => checkbox.dataset.whatsappUrl);
                const openedWindows = selectedUrls.map((url) => {
                    const openedWindow = window.open('about:blank', '_blank');

                    if (openedWindow) {
                        openedWindow.opener = null;
                        openedWindow.location.href = url;
                    }

                    return openedWindow;
                });

                if (openedWindows.some((openedWindow) => openedWindow === null)) {
                    window.alert('Sebagian WhatsApp tidak dapat dibuka. Izinkan pop-up untuk situs ini, lalu klik Kirim Massal lagi.');
                }
            });
        </script>
    </body>
</html>
