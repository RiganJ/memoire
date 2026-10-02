@extends('customer.layouts.app')
@section('title', 'Edit Tamu')
@section('content')
    <div class="mx-auto max-w-2xl"><a href="{{ route('customer.dashboard') }}" class="text-xs text-[#582308]/50"><i class="fa-solid fa-arrow-left mr-2"></i>Kembali ke dashboard</a><h1 class="mt-4 font-display text-4xl text-[#582308]">Edit tamu</h1><p class="mt-2 text-sm text-[#32170b]/45">Perbarui data {{ $guest->name }} dan status RSVP-nya.</p><form method="POST" action="{{ route('customer.guests.update', $guest) }}" class="mt-7">@csrf @method('PUT') @include('customer.guests.partials.form', ['submitLabel' => 'Simpan perubahan'])</form></div>
@endsection
