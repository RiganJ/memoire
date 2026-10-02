<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'in:waiting,process,revision,ready,done,cancelled'],
        ]);

        $orders = Order::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('order_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $summary = [
            'all' => Order::count(),
            'process' => Order::where('status', 'process')->count(),
            'waiting' => Order::whereIn('status', ['waiting', 'revision'])->count(),
            'done' => Order::where('status', 'done')->count(),
        ];

        return view('admin.orders.index', compact('orders', 'summary'));
    }

    public function create(): View
    {
        return view('admin.orders.create');
    }

    public function store(OrderRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['order_number'] = $data['order_number'] ?: $this->nextOrderNumber();
        $order = Order::create($data);

        return redirect()->route('admin.orders.show', $order)->with('success', 'Pesanan berhasil ditambahkan.');
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', compact('order'));
    }

    public function edit(Order $order): View
    {
        return view('admin.orders.edit', compact('order'));
    }

    public function update(OrderRequest $request, Order $order): RedirectResponse
    {
        $data = $request->validated();
        $data['order_number'] = $data['order_number'] ?: $order->order_number;
        $order->update($data);

        return redirect()->route('admin.orders.show', $order)->with('success', 'Pesanan berhasil diperbarui.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', 'Pesanan berhasil dihapus.');
    }

    private function nextOrderNumber(): string
    {
        $nextId = (int) Order::max('id') + 1;

        do {
            $number = 'INV-'.str_pad((string) $nextId++, 4, '0', STR_PAD_LEFT);
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
