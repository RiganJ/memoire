<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\InvitationGuest;
use App\Services\InvitationTemplateRenderer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use RuntimeException;

class PublicInvitationController extends Controller
{
    public function adapter(): Response
    {
        return response(file_get_contents(public_path('invitation-rsvp.js')))
            ->header('Content-Type', 'application/javascript; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function show(
        string $invitationSlug,
        InvitationTemplateRenderer $renderer,
        ?string $guestSlug = null,
    ): Response {
        $invitation = Invitation::query()
            ->with('template')
            ->where('slug', $invitationSlug)
            ->where('status', 'published')
            ->whereHas('template', fn (Builder $query) => $query->where('status', 'published'))
            ->firstOrFail();

        $guest = $guestSlug === null
            ? null
            : $invitation->guests()->where('slug', $guestSlug)->firstOrFail();

        if ($guest !== null) {
            $guest->increment('open_count', 1, [
                'opened_at' => $guest->opened_at ?? now(),
            ]);
        }

        try {
            $html = $renderer->render($invitation, $guest);
        } catch (RuntimeException) {
            abort(404);
        }

        return response($html)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    public function responses(string $invitationSlug, string $guestSlug): JsonResponse
    {
        [$invitation] = $this->invitationAndGuest($invitationSlug, $guestSlug);

        return response()->json([
            'rsvps' => $invitation->guests()
                ->whereNotNull('rsvp_responded_at')
                ->latest('rsvp_responded_at')
                ->limit(100)
                ->get()
                ->map(fn (InvitationGuest $guest): array => $this->responsePayload($guest)),
        ]);
    }

    public function respond(Request $request, string $invitationSlug, string $guestSlug): JsonResponse
    {
        [, $guest] = $this->invitationAndGuest($invitationSlug, $guestSlug);
        $validated = $request->validate([
            'attendance' => ['nullable', Rule::in(['attending', 'declined'])],
            'guest_count' => ['nullable', 'integer', 'min:1', 'max:20'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        if (! isset($validated['attendance']) && blank($validated['message'] ?? null)) {
            return response()->json(['message' => 'Pilih kehadiran atau tuliskan ucapan.'], 422);
        }

        $updates = [];
        if (isset($validated['attendance'])) {
            $updates['rsvp_status'] = $validated['attendance'];
            $updates['rsvp_responded_at'] = now();
            $updates['party_size'] = $validated['attendance'] === InvitationGuest::RSVP_ATTENDING
                ? ($validated['guest_count'] ?? 1)
                : null;
        }
        if (filled($validated['message'] ?? null)) {
            $updates['wish_message'] = trim($validated['message']);
            $updates['wish_submitted_at'] = now();
        }

        $guest->update($updates);

        return response()->json(['rsvp' => $this->responsePayload($guest->refresh())]);
    }

    /** @return array{0: Invitation, 1: InvitationGuest} */
    private function invitationAndGuest(string $invitationSlug, string $guestSlug): array
    {
        $invitation = Invitation::query()
            ->where('slug', $invitationSlug)
            ->where('status', 'published')
            ->firstOrFail();
        $guest = $invitation->guests()->where('slug', $guestSlug)->firstOrFail();

        return [$invitation, $guest];
    }

    /** @return array<string, int|string|null> */
    private function responsePayload(InvitationGuest $guest): array
    {
        return [
            'guest_name' => $guest->name,
            'attendance' => $guest->rsvp_status,
            'guest_count' => $guest->party_size,
            'message' => $guest->wish_message,
            'created_at' => ($guest->wish_submitted_at ?? $guest->rsvp_responded_at)?->toIso8601String(),
        ];
    }
}
