<?php

namespace App\Http\Middleware;

use App\Models\Invitation;
use App\Models\Order;
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
        $orderId = $request->session()->get('customer_order_id');
        $order = is_numeric($orderId)
            ? Order::query()->with('servicePackage')->where('payment_status', 'paid')->find($orderId)
            : null;

        if ($order !== null && ! $order->includesCustomerPortal()) {
            $order = null;
        }

        if ($invitation === null && $order === null) {
            $request->session()->forget(['customer_invitation_id', 'customer_order_id']);

            return redirect()->guest(route('customer.login'));
        }

        if ($invitation !== null) {
            $request->attributes->set('customerInvitation', $invitation);
        }
        if ($order !== null) {
            $request->attributes->set('customerOrder', $order);
        }

        return $next($request);
    }
}
