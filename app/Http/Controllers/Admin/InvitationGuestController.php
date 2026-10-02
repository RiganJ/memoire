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
            'rsvp_responded_at' => isset($validated['rsvp_status']) && $validated['rsvp_status'] !== InvitationGuest::RSVP_PENDING ? now() : null,
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
        $updates = [
            ...$validated,
            'slug' => $invitation->uniqueGuestSlug($validated['name'], $guest),
        ];

        if (isset($validated['rsvp_status'])) {
            $updates['rsvp_responded_at'] = $validated['rsvp_status'] === InvitationGuest::RSVP_PENDING ? null : now();
        }

        $guest->update($updates);

        return redirect()->route('admin.invitations.show', $invitation)->with('success', 'Data tamu berhasil diperbarui.');
    }

    public function destroy(Invitation $invitation, InvitationGuest $guest): RedirectResponse
    {
        $guest->delete();

        return redirect()->route('admin.invitations.show', $invitation)->with('success', 'Tamu berhasil dihapus.');
    }
}
