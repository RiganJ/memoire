<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Services\InvitationTemplateRenderer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use RuntimeException;

class PublicInvitationController extends Controller
{
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

        try {
            $html = $renderer->render($invitation, $guest);
        } catch (RuntimeException) {
            abort(404);
        }

        return response($html)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('X-Content-Type-Options', 'nosniff');
    }
}
