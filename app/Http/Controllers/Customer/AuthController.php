<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CustomerLoginRequest;
use App\Models\Invitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('customer_invitation_id')) {
            return redirect()->route('customer.dashboard');
        }

        return view('customer.auth.login');
    }

    public function store(CustomerLoginRequest $request): RedirectResponse
    {
        $invitation = Invitation::query()
            ->where('customer_access_code', $request->validated('access_code'))
            ->first();

        if ($invitation === null) {
            return back()->withInput()->withErrors([
                'access_code' => 'Kode akses tidak ditemukan. Periksa kembali kode dari admin.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('customer_invitation_id', $invitation->getKey());

        return redirect()->intended(route('customer.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget('customer_invitation_id');
        $request->session()->regenerateToken();

        return redirect()->route('customer.login')->with('success', 'Anda berhasil keluar dari portal customer.');
    }
}
