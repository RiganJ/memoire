<?php

namespace App\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected $fillable = ['order_id', 'payment_method_id', 'transaction_number', 'amount', 'status', 'paid_at', 'notes'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'paid_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public static function syncOrderStatus(Order $order): void
    {
        $paymentStatus = match (true) {
            $order->payments()->where('status', 'success')->exists() => 'paid',
            $order->payments()->where('status', 'refunded')->exists() => 'refunded',
            default => 'unpaid',
        };

        $order->update(['payment_status' => $paymentStatus]);
    }
}
