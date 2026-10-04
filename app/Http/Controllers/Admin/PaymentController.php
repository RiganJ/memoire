<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\InvoiceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        $payments = $this->filteredPayments($filters)->with(['order', 'paymentMethod'])->latest('paid_at')->latest()->paginate(10)->withQueryString();
        $now = now();
        $successfulPayments = Payment::query()->whereIn('status', ['success', 'paid']);
        $refundedPayments = Payment::query()->where('status', 'refunded');
        $summary = [
            'month_revenue' => (clone $successfulPayments)->whereBetween('paid_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])->sum('amount'),
            'week_revenue' => (clone $successfulPayments)->whereBetween('paid_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])->sum('amount'),
            'week_transactions' => (clone $successfulPayments)->whereBetween('paid_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])->count(),
            'pending' => Payment::query()->where('status', 'pending')->count(),
            'refunded' => (clone $refundedPayments)->whereBetween('paid_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])->sum('amount'),
            'refund_transactions' => (clone $refundedPayments)->whereBetween('paid_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])->count(),
            'available_balance' => Payment::query()->whereIn('status', ['success', 'paid'])->sum('amount') - Payment::query()->where('status', 'refunded')->sum('amount'),
        ];

        return view('admin.payments.index', [
            'payments' => $payments,
            'paymentMethods' => PaymentMethod::query()->withCount('payments')->orderBy('sort_order')->orderBy('id')->get(),
            'activePaymentMethods' => PaymentMethod::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'primaryPaymentMethod' => PaymentMethod::query()->where('is_active', true)->orderBy('sort_order')->first(),
            'summary' => $summary,
            'chart' => $this->revenueChart((int) ($filters['period'] ?? 6)),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('admin.payments.create', $this->formData());
    }

    public function store(PaymentRequest $request, InvoiceService $invoices): RedirectResponse
    {
        $data = $request->validated();
        $data['transaction_number'] = ($data['transaction_number'] ?? null) ?: $this->nextTransactionNumber();
        $payment = Payment::create($data);
        Payment::syncOrderStatus($payment->order);
        $this->createInvoiceForSuccessfulPayment($payment, $invoices);

        return redirect()->route('admin.payments.show', $payment)->with('success', 'Pembayaran berhasil ditambahkan.');
    }

    public function show(Payment $payment): View
    {
        return view('admin.payments.show', ['payment' => $payment->load(['order', 'paymentMethod', 'invoice'])]);
    }

    public function edit(Payment $payment): View
    {
        return view('admin.payments.edit', [...$this->formData(), 'payment' => $payment]);
    }

    public function update(PaymentRequest $request, Payment $payment, InvoiceService $invoices): RedirectResponse
    {
        $previousOrder = $payment->order;
        $data = $request->validated();
        $data['transaction_number'] = ($data['transaction_number'] ?? null) ?: $payment->transaction_number;
        $payment->update($data);
        Payment::syncOrderStatus($previousOrder);
        Payment::syncOrderStatus($payment->fresh()->order);
        $this->createInvoiceForSuccessfulPayment($payment->fresh(), $invoices);

        return redirect()->route('admin.payments.show', $payment)->with('success', 'Pembayaran berhasil diperbarui.');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $order = $payment->order;
        $payment->delete();
        Payment::syncOrderStatus($order);

        return redirect()->route('admin.payments.index')->with('success', 'Pembayaran berhasil dihapus.');
    }

    public function export(Request $request): StreamedResponse
    {
        $payments = $this->filteredPayments($this->validatedFilters($request))->with(['order', 'paymentMethod'])->latest()->get();

        return response()->streamDownload(function () use ($payments): void {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, ['Nomor Transaksi', 'Nomor Pesanan', 'Pelanggan', 'Metode', 'Nominal', 'Status', 'Tanggal']);
            foreach ($payments as $payment) {
                fputcsv($stream, [$payment->transaction_number, $payment->order->order_number, $payment->order->customer_name, $payment->paymentMethod->name, $payment->amount, $payment->status, $payment->paid_at?->format('Y-m-d H:i:s')]);
            }
            fclose($stream);
        }, 'laporan-pembayaran-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    /** @return array{orders: Collection<int, Order>, paymentMethods: Collection<int, PaymentMethod>} */
    private function formData(): array
    {
        return [
            'orders' => Order::query()->latest()->get(['id', 'order_number', 'customer_name', 'total']),
            'paymentMethods' => PaymentMethod::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ];
    }

    /** @return array{search?: string|null, status?: string|null, payment_method_id?: int|null, period?: int|null} */
    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'in:pending,success,paid,failed,expired,cancelled,refunded'],
            'payment_method_id' => ['nullable', 'integer', 'exists:payment_methods,id'],
            'period' => ['nullable', 'integer', 'in:6,12'],
        ]);
    }

    /** @param array{search?: string|null, status?: string|null, payment_method_id?: int|null, period?: int|null} $filters */
    private function filteredPayments(array $filters): Builder
    {
        return Payment::query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('transaction_number', 'like', "%{$search}%")
                        ->orWhereHas('order', fn (Builder $query) => $query->where('order_number', 'like', "%{$search}%")->orWhere('customer_name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['payment_method_id'] ?? null, fn (Builder $query, int $methodId) => $query->where('payment_method_id', $methodId));
    }

    /** @return Collection<int, array{label: string, amount: float, percentage: float}> */
    private function revenueChart(int $period): Collection
    {
        $months = collect(range($period - 1, 0))->map(fn (int $monthsAgo) => now()->copy()->subMonths($monthsAgo)->startOfMonth());
        $amounts = $months->map(fn ($month) => (float) Payment::query()->whereIn('status', ['success', 'paid'])->whereBetween('paid_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])->sum('amount'));
        $maximum = max(1, (float) $amounts->max());

        return $months->map(fn ($month, int $index) => [
            'label' => $month->translatedFormat('M'),
            'amount' => $amounts[$index],
            'percentage' => max(4, ($amounts[$index] / $maximum) * 100),
        ]);
    }

    private function nextTransactionNumber(): string
    {
        $nextId = (int) Payment::max('id') + 1;
        do {
            $number = 'PAY-'.str_pad((string) $nextId++, 4, '0', STR_PAD_LEFT);
        } while (Payment::where('transaction_number', $number)->exists());

        return $number;
    }

    private function createInvoiceForSuccessfulPayment(Payment $payment, InvoiceService $invoices): void
    {
        if (! in_array($payment->status, ['success', 'paid'], true)) {
            return;
        }

        $invoices->createForPayment($payment->load('order.servicePackage', 'servicePackage'));
    }
}
