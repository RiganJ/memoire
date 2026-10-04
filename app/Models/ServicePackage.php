<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServicePackage extends Model
{
    public const CUSTOMER_PORTAL_PACKAGES = ['intimate', 'timeless'];

    protected $fillable = ['name', 'price', 'description', 'features', 'badge', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['features' => 'array', 'is_active' => 'boolean'];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public static function includesCustomerPortal(string $packageName): bool
    {
        return in_array(mb_strtolower(trim($packageName)), self::CUSTOMER_PORTAL_PACKAGES, true);
    }
}
