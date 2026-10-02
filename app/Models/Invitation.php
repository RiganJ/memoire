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
    ];

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
