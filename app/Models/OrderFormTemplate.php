<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderFormTemplate extends Model
{
    public const BASIC_FIELDS = [
        ['key' => 'event_name', 'label' => 'Nama acara', 'type' => 'text', 'required' => true, 'options' => []],
        ['key' => 'event_date', 'label' => 'Tanggal acara', 'type' => 'date', 'required' => true, 'options' => []],
        ['key' => 'event_time', 'label' => 'Waktu acara', 'type' => 'text', 'required' => true, 'options' => []],
        ['key' => 'event_location', 'label' => 'Lokasi acara', 'type' => 'textarea', 'required' => true, 'options' => []],
        ['key' => 'opening_text', 'label' => 'Teks pembuka', 'type' => 'textarea', 'required' => false, 'options' => []],
        ['key' => 'additional_notes', 'label' => 'Catatan tambahan', 'type' => 'textarea', 'required' => false, 'options' => []],
    ];

    protected $fillable = ['catalog_id', 'name', 'category', 'description', 'fields'];

    protected function casts(): array
    {
        return ['fields' => 'array'];
    }

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(Catalog::class);
    }
}
