<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\InvitationGuestRequest;
use App\Models\Invitation;
use App\Models\InvitationGuest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InvitationGuestController extends Controller
{
    public function create(Request $request): View
    {
        return view('customer.guests.create', ['invitation' => $this->invitation($request)]);
    }

    public function store(InvitationGuestRequest $request): RedirectResponse
    {
        $invitation = $this->invitation($request);
        $validated = $request->validated();
        $invitation->guests()->create([
            ...$validated,
            'slug' => $invitation->uniqueGuestSlug($validated['name']),
            'token' => (string) Str::uuid(),
            'rsvp_responded_at' => $validated['rsvp_status'] === InvitationGuest::RSVP_PENDING ? null : now(),
        ]);

        return redirect()->route('customer.dashboard')->with('success', 'Tamu berhasil ditambahkan.');
    }

    public function edit(Request $request, InvitationGuest $guest): View
    {
        $invitation = $this->invitation($request);
        $this->ensureGuestBelongsToInvitation($guest, $invitation);

        return view('customer.guests.edit', compact('invitation', 'guest'));
    }

    public function update(InvitationGuestRequest $request, InvitationGuest $guest): RedirectResponse
    {
        $invitation = $this->invitation($request);
        $this->ensureGuestBelongsToInvitation($guest, $invitation);
        $validated = $request->validated();
        $guest->update([
            ...$validated,
            'slug' => $invitation->uniqueGuestSlug($validated['name'], $guest),
            'rsvp_responded_at' => $validated['rsvp_status'] === InvitationGuest::RSVP_PENDING ? null : now(),
        ]);

        return redirect()->route('customer.dashboard')->with('success', 'Data tamu berhasil diperbarui.');
    }

    public function destroy(Request $request, InvitationGuest $guest): RedirectResponse
    {
        $invitation = $this->invitation($request);
        $this->ensureGuestBelongsToInvitation($guest, $invitation);
        $guest->delete();

        return redirect()->route('customer.dashboard')->with('success', 'Tamu berhasil dihapus.');
    }

    private function invitation(Request $request): Invitation
    {
        $invitation = $request->attributes->get('customerInvitation');
        abort_unless($invitation instanceof Invitation, 403);

        return $invitation;
    }

    private function ensureGuestBelongsToInvitation(InvitationGuest $guest, Invitation $invitation): void
    {
        abort_unless($guest->invitation_id === $invitation->getKey(), 404);
    }
}
