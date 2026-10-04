<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $successfulPayments = Payment::query()->whereIn('status', ['success', 'paid']);
        $monthOrders = Order::query()->whereBetween('created_at', [$monthStart, $monthEnd]);
        $monthOrderCount = (clone $monthOrders)->count();
        $monthPaidOrderCount = (clone $monthOrders)->where('payment_status', 'paid')->count();

        $summary = [
            'active_orders' => Order::query()->whereNotIn('status', ['done', 'cancelled'])->count(),
            'waiting_orders' => Order::query()->whereIn('status', ['waiting', 'revision'])->count(),
            'completed_orders' => Order::query()->where('status', 'done')->whereBetween('updated_at', [$monthStart, $monthEnd])->count(),
            'month_revenue' => (clone $successfulPayments)->whereBetween('paid_at', [$monthStart, $monthEnd])->sum('amount'),
            'total_orders' => Order::query()->count(),
            'successful_payments' => (clone $successfulPayments)->count(),
            'pending_payments' => Payment::query()->where('status', 'pending')->count(),
            'customers' => Customer::query()->count(),
            'active_chats' => Conversation::query()->active()->count(),
        ];

        return view('admin.dashboard', [
            'summary' => $summary,
            'recentOrders' => Order::query()->latest()->limit(5)->get(),
            'activities' => $this->recentActivities(),
            'monthLabel' => now()->locale('id')->translatedFormat('F Y'),
            'monthOrderCount' => $monthOrderCount,
            'monthPaidOrderCount' => $monthPaidOrderCount,
            'monthPaidPercentage' => $monthOrderCount === 0 ? 0 : (int) round(($monthPaidOrderCount / $monthOrderCount) * 100),
        ]);
    }

    /** @return Collection<int, array{icon: string, description: string, occurred_at: Carbon, url: string}> */
    private function recentActivities(): Collection
    {
        $orders = Order::query()->latest()->limit(4)->get()->map(fn (Order $order): array => [
            'icon' => 'fa-envelope-open-text',
            'description' => 'Pesanan '.$order->order_number.' dibuat oleh '.$order->customer_name,
            'occurred_at' => $order->created_at,
            'url' => route('admin.orders.show', $order),
        ]);

        $payments = Payment::query()->with('order')->latest()->limit(4)->get()->map(fn (Payment $payment): array => [
            'icon' => in_array($payment->status, ['success', 'paid'], true) ? 'fa-circle-check' : 'fa-credit-card',
            'description' => 'Pembayaran '.($payment->transaction_number ?: $payment->partner_reference_no).' untuk '.$payment->order->order_number,
            'occurred_at' => $payment->updated_at,
            'url' => route('admin.payments.show', $payment),
        ]);

        $conversations = Conversation::query()->latest('last_message_at')->limit(4)->get()->map(fn (Conversation $conversation): array => [
            'icon' => 'fa-comment',
            'description' => 'Percakapan '.$conversation->ticket_number.' dari '.$conversation->guest_name,
            'occurred_at' => $conversation->last_message_at ?? $conversation->updated_at,
            'url' => route('admin.chats.show', $conversation),
        ]);

        return $orders
            ->concat($payments)
            ->concat($conversations)
            ->sortByDesc('occurred_at')
            ->take(5)
            ->values();
    }
}
