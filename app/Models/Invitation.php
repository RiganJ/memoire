<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Invitation extends Model
{
    public const RESERVED_SLUGS = [
        'admin',
        'api',
        'assets',
        'build',
        'customer',
        'dashboard',
        'favicon-ico',
        'fonts',
        'form-pesanan',
        'images',
        'invitation-assets',
        'katalog',
        'live-chat',
        'login',
        'logout',
        'paket-harga',
        'pelanggan',
        'pembayaran',
        'pengaturan',
        'pemesanan',
        'pesanan',
        'robots-txt',
        'storage',
        'template-undangan',
        'up',
    ];

    protected $fillable = [
        'template_id',
        'name',
        'slug',
        'groom_name',
        'bride_name',
        'status',
        'customer_access_code',
    ];

    protected static function booted(): void
    {
        static::creating(function (Invitation $invitation): void {
            $invitation->customer_access_code ??= self::generateUniqueCustomerAccessCode();
        });
    }

    public static function generateUniqueCustomerAccessCode(): string
    {
        do {
            $accessCode = 'MEMOIRE-'.Str::upper(Str::random(8));
        } while (self::query()->where('customer_access_code', $accessCode)->exists());

        return $accessCode;
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(InvitationGuest::class);
    }

    public function uniqueGuestSlug(string $name, ?InvitationGuest $except = null): string
    {
        $baseSlug = Str::slug($name) ?: 'tamu';
        $slug = Str::limit($baseSlug, 100, '');
        $suffix = 2;

        while ($this->guests()
            ->when($except, fn ($query) => $query->whereKeyNot($except->getKey()))
            ->where('slug', $slug)
            ->exists()) {
            $suffixText = '-'.$suffix;
            $slug = Str::limit($baseSlug, 100 - strlen($suffixText), '').$suffixText;
            $suffix++;
        }

        return $slug;
    }
}
