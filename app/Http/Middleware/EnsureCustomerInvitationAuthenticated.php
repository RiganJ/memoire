<?php

namespace App\Http\Middleware;

use App\Models\Invitation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerInvitationAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $invitationId = $request->session()->get('customer_invitation_id');
        $invitation = is_numeric($invitationId) ? Invitation::query()->find($invitationId) : null;

        if ($invitation === null) {
            $request->session()->forget('customer_invitation_id');

            return redirect()->guest(route('customer.login'));
        }

        $request->attributes->set('customerInvitation', $invitation);

        return $next($request);
    }
}
