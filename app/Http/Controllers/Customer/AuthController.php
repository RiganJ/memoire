<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CustomerLoginRequest;
use App\Models\Invitation;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('customer_invitation_id') || $request->session()->has('customer_order_id')) {
            return redirect()->route('customer.dashboard');
        }

        return view('customer.auth.login');
    }

    public function store(CustomerLoginRequest $request): RedirectResponse
    {
        $invitation = Invitation::query()
            ->where('customer_access_code', $request->validated('access_code'))
            ->first();

        if ($invitation !== null) {
            $request->session()->regenerate();
            $request->session()->forget('customer_order_id');
            $request->session()->put('customer_invitation_id', $invitation->getKey());

            return redirect()->intended(route('customer.dashboard'));
        }

        $order = Order::query()
            ->with('servicePackage')
            ->where('customer_access_code', $request->validated('access_code'))
            ->where('payment_status', 'paid')
            ->first();

        if ($order === null || ! $order->includesCustomerPortal()) {
            return back()->withInput()->withErrors([
                'access_code' => 'Kode akses tidak ditemukan atau pembayaran belum berhasil.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->forget('customer_invitation_id');
        $request->session()->put('customer_order_id', $order->getKey());

        return redirect()->intended(route('customer.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget(['customer_invitation_id', 'customer_order_id']);
        $request->session()->regenerateToken();

        return redirect()->route('customer.login')->with('success', 'Anda berhasil keluar dari portal customer.');
    }
}
