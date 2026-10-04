<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    public const RESERVED_SLUGS = [
        'admin', 'api', 'assets', 'build', 'dashboard', 'favicon-ico', 'fonts',
        'form-pesanan', 'images', 'katalog', 'live-chat', 'login', 'logout',
        'paket-harga', 'pelanggan', 'pembayaran', 'pengaturan', 'pemesanan',
        'pesanan', 'preview-template', 'robots-txt', 'storage', 'templates', 'up',
    ];

    protected $fillable = [
        'name',
        'slug',
        'folder_name',
        'status',
    ];

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }
}
