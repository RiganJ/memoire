<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'uuid',
        'customer_id',
        'catalog_id',
        'service_package_id',
        'customer_name',
        'email',
        'phone',
        'package',
        'event_type',
        'form_category',
        'form_data',
        'total',
        'event_date',
        'status',
        'payment_status',
        'customer_access_code',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'total' => 'decimal:2',
            'form_data' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(Catalog::class);
    }

    public function servicePackage(): BelongsTo
    {
        return $this->belongsTo(ServicePackage::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function includesCustomerPortal(): bool
    {
        return ServicePackage::includesCustomerPortal($this->servicePackage?->name ?? $this->package);
    }

    public function grantCustomerPortalAccess(): void
    {
        if ($this->payment_status !== 'paid' || ! $this->includesCustomerPortal() || $this->customer_access_code !== null) {
            return;
        }

        do {
            $accessCode = 'MEMOIRE-'.Str::upper(Str::random(8));
        } while (
            self::query()->where('customer_access_code', $accessCode)->exists()
            || Invitation::query()->where('customer_access_code', $accessCode)->exists()
        );

        $this->update(['customer_access_code' => $accessCode]);
    }
}
