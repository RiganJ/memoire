<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InvitationGuestRequest;
use App\Models\Invitation;
use App\Models\InvitationGuest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InvitationGuestController extends Controller
{
    public function create(Invitation $invitation): View
    {
        return view('admin.invitation-guests.create', compact('invitation'));
    }

    public function store(InvitationGuestRequest $request, Invitation $invitation): RedirectResponse
    {
        $validated = $request->validated();

        $invitation->guests()->create([
            ...$validated,
            'slug' => $invitation->uniqueGuestSlug($validated['name']),
            'token' => (string) Str::uuid(),
        ]);

        return redirect()->route('admin.invitations.show', $invitation)->with('success', 'Tamu berhasil ditambahkan.');
    }

    public function edit(Invitation $invitation, InvitationGuest $guest): View
    {
        return view('admin.invitation-guests.edit', compact('invitation', 'guest'));
    }

    public function update(InvitationGuestRequest $request, Invitation $invitation, InvitationGuest $guest): RedirectResponse
    {
        $validated = $request->validated();

        $guest->update([
            ...$validated,
            'slug' => $invitation->uniqueGuestSlug($validated['name'], $guest),
        ]);

        return redirect()->route('admin.invitations.show', $invitation)->with('success', 'Data tamu berhasil diperbarui.');
    }

    public function destroy(Invitation $invitation, InvitationGuest $guest): RedirectResponse
    {
        $guest->delete();

        return redirect()->route('admin.invitations.show', $invitation)->with('success', 'Tamu berhasil dihapus.');
    }
}
