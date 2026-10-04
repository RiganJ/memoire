<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $order->order_number }} — Memoire Admin</title>@vite('resources/css/app.css')</head>
<body class="bg-[#f4efe7] text-[#32170b] antialiased">
    @php
        $statusLabels = ['waiting' => 'Menunggu data', 'process' => 'Dalam proses', 'revision' => 'Revisi', 'ready' => 'Siap dikirim', 'done' => 'Selesai', 'cancelled' => 'Dibatalkan'];
        $paymentLabels = ['unpaid' => 'Belum lunas', 'paid' => 'Lunas', 'refunded' => 'Dikembalikan'];
        $whatsappNumber = preg_replace('/\D+/', '', $order->phone ?? '');
        $whatsappNumber = str_starts_with($whatsappNumber, '0') ? '62'.substr($whatsappNumber, 1) : (str_starts_with($whatsappNumber, '8') ? '62'.$whatsappNumber : $whatsappNumber);
        $customerLoginUrl = route('customer.login');
        $whatsappMessage = "Halo {$order->customer_name}, pembayaran untuk pesanan {$order->order_number} sudah kami konfirmasi lunas.\n\nKode akses dashboard customer Anda:\n{$order->customer_access_code}\n\nLogin di: {$customerLoginUrl}\n\nSimpan kode ini untuk mengakses dashboard Memoire. Terima kasih telah mempercayakan momen istimewa Anda kepada kami.";
    @endphp
    <div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">@include('admin.partials.sidebar')<main class="min-w-0">
        <header class="sticky top-0 z-30 flex h-20 items-center justify-between border-b border-[#582308]/10 bg-[#f4efe7]/90 px-5 backdrop-blur-xl sm:px-8 lg:h-24 lg:px-10"><div><a href="{{ route('admin.orders.index') }}" class="text-xs text-[#32170b]/45"><i class="fa-solid fa-arrow-left mr-2"></i>Semua pesanan</a><h1 class="mt-2 font-display text-2xl text-[#582308] sm:text-3xl">{{ $order->order_number }}</h1></div><a href="{{ route('admin.orders.edit', $order) }}" class="flex h-11 items-center gap-2 rounded-full bg-[#582308] px-5 text-sm font-semibold text-white"><i class="fa-solid fa-pen text-xs"></i><span class="hidden sm:inline">Edit Pesanan</span></a></header>
        <div class="space-y-6 p-5 sm:p-8 lg:p-10">
            @if (session('success'))<div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800"><i class="fa-solid fa-circle-check"></i>{{ session('success') }}</div>@endif
            <div class="grid gap-6 xl:grid-cols-[1.5fr_.7fr]">
                <section class="rounded-3xl border border-[#582308]/8 bg-white p-6 shadow-[0_10px_35px_rgba(88,35,8,.04)] sm:p-8"><div class="flex flex-col gap-4 border-b border-[#582308]/8 pb-6 sm:flex-row sm:items-center sm:justify-between"><div><p class="text-[10px] font-bold uppercase tracking-[.18em] text-[#32170b]/40">Pelanggan</p><h2 class="mt-2 font-display text-3xl text-[#582308]">{{ $order->customer_name }}</h2></div><span class="admin-status admin-status-{{ $order->status }} w-fit"><span></span>{{ $statusLabels[$order->status] }}</span></div>
                    <dl class="mt-6 grid gap-x-8 gap-y-6 sm:grid-cols-2">@foreach ([['Email', $order->email], ['WhatsApp', $order->phone ?: 'Belum diisi'], ['Paket', $order->package], ['Jenis acara', $order->event_type], ['Tanggal acara', $order->event_date?->translatedFormat('d F Y') ?? 'Belum ditentukan'], ['Dibuat', $order->created_at->translatedFormat('d F Y, H.i')]] as [$term, $value])<div><dt class="text-[9px] font-bold uppercase tracking-[.16em] text-[#32170b]/35">{{ $term }}</dt><dd class="mt-2 text-sm font-medium text-[#32170b]/75">{{ $value }}</dd></div>@endforeach</dl>
                    <div class="mt-8 rounded-2xl bg-[#faf7f0] p-5"><p class="text-[9px] font-bold uppercase tracking-[.16em] text-[#32170b]/35">Catatan</p><p class="mt-3 whitespace-pre-line text-sm leading-7 text-[#32170b]/60">{{ $order->notes ?: 'Tidak ada catatan untuk pesanan ini.' }}</p></div>
                    @if ($order->form_data)<div class="mt-5 rounded-2xl border border-[#582308]/10 bg-white p-5"><p class="text-[9px] font-bold uppercase tracking-[.16em] text-[#32170b]/35">Data formulir {{ $order->form_category }}</p><dl class="mt-4 grid gap-4 sm:grid-cols-2">@foreach ($order->form_data as $label => $value)<div><dt class="text-xs font-semibold text-[#582308]">{{ str($label)->replace('_', ' ')->title() }}</dt><dd class="mt-1 whitespace-pre-line text-sm text-[#32170b]/60">{{ $value ?: '—' }}</dd></div>@endforeach</dl></div>@endif
                    @if($order->payments->isNotEmpty())
                        <div class="mt-5 rounded-2xl border border-[#582308]/10 bg-white p-5">
                            <p class="text-[9px] font-bold uppercase tracking-[.16em] text-[#32170b]/35">Riwayat pembayaran</p>
                            <div class="mt-4 space-y-3">
                                @foreach($order->payments->sortByDesc('created_at') as $payment)
                                    <a href="{{ route('admin.payments.show', $payment) }}" class="flex flex-col gap-2 rounded-xl bg-[#faf7f0] p-4 transition hover:bg-[#f4e8d3] sm:flex-row sm:items-center sm:justify-between">
                                        <span><strong class="block text-xs text-[#582308]">{{ $payment->transaction_number }} · {{ $payment->paymentMethod->name }}</strong><small class="mt-1 block text-[10px] text-[#32170b]/45">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}{{ $payment->proof_path ? ' · Bukti terlampir' : '' }}</small></span>
                                        <span class="text-[10px] font-bold uppercase text-[#582308]">{{ $payment->status }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>
                <aside class="space-y-5"><section class="rounded-3xl bg-[#582308] p-6 text-white shadow-[0_20px_50px_rgba(88,35,8,.16)]"><p class="text-[9px] font-bold uppercase tracking-[.2em] text-[#ead5ac]/70">Nilai pesanan</p><p class="mt-3 font-display text-3xl text-[#ead5ac]">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</p><div class="mt-6 flex items-center justify-between border-t border-white/10 pt-5 text-xs"><span class="text-white/45">Pembayaran</span><span class="font-semibold text-[#ead5ac]">{{ $paymentLabels[$order->payment_status] }}</span></div></section>
                    @if($order->payment_status === 'paid' && $order->customer_access_code && $order->includesCustomerPortal())
                        <section class="rounded-3xl border border-emerald-200 bg-white p-6">
                            <p class="text-[9px] font-bold uppercase tracking-[.18em] text-emerald-700">Akses dashboard customer</p>
                            <p class="mt-3 font-mono text-xl font-bold tracking-wide text-[#582308]">{{ $order->customer_access_code }}</p>
                            <a href="{{ $customerLoginUrl }}" target="_blank" rel="noopener" class="mt-3 block break-all text-xs text-[#582308] underline">{{ $customerLoginUrl }}</a>
                            @if($whatsappNumber !== '')
                                <a href="https://wa.me/{{ $whatsappNumber }}?text={{ rawurlencode($whatsappMessage) }}" target="_blank" rel="noopener" class="mt-5 flex min-h-11 items-center justify-center gap-2 rounded-xl bg-emerald-700 px-4 text-xs font-bold text-white"><i class="fa-brands fa-whatsapp"></i>Kirim kode & link ke WhatsApp</a>
                                <details class="mt-4"><summary class="cursor-pointer text-[10px] font-semibold text-[#582308]/65">Lihat template pesan</summary><p class="mt-2 whitespace-pre-line rounded-xl bg-[#faf7f0] p-3 text-[10px] leading-5 text-[#32170b]/60">{{ $whatsappMessage }}</p></details>
                            @else
                                <p class="mt-4 rounded-xl bg-amber-50 p-3 text-xs text-amber-800">Nomor WhatsApp customer belum tersedia.</p>
                            @endif
                        </section>
                    @endif
                    @if($order->payment_status === 'paid' && ! $order->includesCustomerPortal())
                        <section class="rounded-3xl border border-[#582308]/10 bg-white p-6"><p class="text-[9px] font-bold uppercase tracking-[.18em] text-[#32170b]/40">Akses dashboard</p><p class="mt-3 text-xs leading-5 text-[#32170b]/55">Paket {{ $order->package }} tidak termasuk akses dashboard customer.</p></section>
                    @endif
                    <section class="rounded-3xl border border-red-100 bg-white p-6"><p class="font-display text-lg text-[#582308]">Hapus pesanan</p><p class="mt-2 text-xs leading-6 text-[#32170b]/45">Data yang dihapus tidak dapat dikembalikan.</p><form class="mt-5" action="{{ route('admin.orders.destroy', $order) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pesanan ini?')">@csrf @method('DELETE')<button class="w-full rounded-xl border border-red-200 py-3 text-xs font-semibold text-red-700 hover:bg-red-50"><i class="fa-regular fa-trash-can mr-2"></i>Hapus Pesanan</button></form></section></aside>
            </div>
        </div>
    </main></div>
</body></html>
