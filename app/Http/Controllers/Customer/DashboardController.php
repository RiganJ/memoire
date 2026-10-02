<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\InvitationGuest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        /** @var Invitation $invitation */
        $invitation = $request->attributes->get('customerInvitation');
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:pending,attending,declined'],
        ]);
        $guestQuery = $invitation->guests();
        $statistics = [
            'sent' => (clone $guestQuery)->count(),
            'opened' => (clone $guestQuery)->whereNotNull('opened_at')->count(),
            'attending' => (clone $guestQuery)->where('rsvp_status', InvitationGuest::RSVP_ATTENDING)->count(),
            'declined' => (clone $guestQuery)->where('rsvp_status', InvitationGuest::RSVP_DECLINED)->count(),
            'pending' => (clone $guestQuery)->where('rsvp_status', InvitationGuest::RSVP_PENDING)->count(),
        ];
        $guests = $guestQuery
            ->when($filters['search'] ?? null, fn (Builder $query, string $term) => $query->where(function (Builder $query) use ($term): void {
                $query->where('name', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%");
            }))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('rsvp_status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('customer.dashboard', compact('invitation', 'statistics', 'guests'));
    }
}
