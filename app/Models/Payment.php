<?php

namespace App\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid', 'order_id', 'payment_method_id', 'service_package_id', 'transaction_number',
        'partner_reference_no', 'dana_reference_no', 'amount', 'currency', 'qr_content',
        'qr_url', 'qr_image', 'status', 'expires_at', 'paid_at', 'failed_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function servicePackage(): BelongsTo
    {
        return $this->belongsTo(ServicePackage::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public static function syncOrderStatus(Order $order): void
    {
        $paymentStatus = match (true) {
            $order->payments()->whereIn('status', ['success', 'paid'])->exists() => 'paid',
            $order->payments()->where('status', 'refunded')->exists() => 'refunded',
            default => 'unpaid',
        };

        $order->update(['payment_status' => $paymentStatus]);
    }
}
