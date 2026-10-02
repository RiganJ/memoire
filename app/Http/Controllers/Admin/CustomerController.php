<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'segment' => ['nullable', 'in:vip,active,new,prospect'],
        ]);

        $customers = Customer::query()
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%")))
            ->when($filters['segment'] ?? null, fn ($query, string $segment) => $query->where('segment', $segment))
            ->orderByDesc('last_active_at')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $summary = [
            'all' => Customer::count(),
            'active' => Customer::where('segment', 'active')->count(),
            'returning' => Customer::where('total_orders', '>', 1)->count(),
            'average' => (float) Customer::avg('total_spent'),
        ];

        return view('admin.customers.index', compact('customers', 'summary'));
    }

    public function create(): View
    {
        return view('admin.customers.create');
    }

    public function store(CustomerRequest $request): RedirectResponse
    {
        $customer = Customer::create($request->validated() + ['last_active_at' => now()]);

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function show(Customer $customer): View
    {
        return view('admin.customers.show', compact('customer'));
    }

    public function edit(Customer $customer): View
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Pelanggan berhasil dihapus.');
    }
}
