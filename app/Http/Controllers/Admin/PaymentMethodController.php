<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaymentMethodRequest;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.payments.index');
    }

    public function create(): View
    {
        return view('admin.payment-methods.create');
    }

    public function store(PaymentMethodRequest $request): RedirectResponse
    {
        PaymentMethod::create($this->data($request));

        return redirect()->route('admin.payments.index')->with('success', 'Metode pembayaran berhasil ditambahkan.');
    }

    public function show(PaymentMethod $paymentMethod): RedirectResponse
    {
        return redirect()->route('admin.payment-methods.edit', $paymentMethod);
    }

    public function edit(PaymentMethod $paymentMethod): View
    {
        return view('admin.payment-methods.edit', compact('paymentMethod'));
    }

    public function update(PaymentMethodRequest $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $paymentMethod->update($this->data($request));

        return redirect()->route('admin.payments.index')->with('success', 'Metode pembayaran berhasil diperbarui.');
    }

    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        if ($paymentMethod->payments()->exists()) {
            return back()->with('error', 'Metode yang sudah digunakan transaksi tidak dapat dihapus. Nonaktifkan metode sebagai gantinya.');
        }

        $paymentMethod->delete();

        return redirect()->route('admin.payments.index')->with('success', 'Metode pembayaran berhasil dihapus.');
    }

    /** @return array<string, mixed> */
    private function data(PaymentMethodRequest $request): array
    {
        return [...$request->validated(), 'is_active' => $request->boolean('is_active')];
    }
}
