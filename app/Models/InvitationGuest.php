<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationGuest extends Model
{
    public const RSVP_PENDING = 'pending';

    public const RSVP_ATTENDING = 'attending';

    public const RSVP_DECLINED = 'declined';

    protected $fillable = [
        'name',
        'slug',
        'phone',
        'token',
        'opened_at',
        'open_count',
        'rsvp_status',
        'rsvp_responded_at',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'open_count' => 'integer',
            'rsvp_responded_at' => 'datetime',
        ];
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
