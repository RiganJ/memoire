<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderFormTemplate extends Model
{
    public const SHARED_CATEGORY = 'Semua Template';

    public const BASIC_FIELDS = [
        ['key' => 'event_name', 'label' => 'Nama acara', 'type' => 'text', 'required' => true, 'options' => []],
        ['key' => 'event_date', 'label' => 'Tanggal acara', 'type' => 'date', 'required' => true, 'options' => []],
        ['key' => 'event_time', 'label' => 'Waktu acara', 'type' => 'text', 'required' => true, 'options' => []],
        ['key' => 'event_location', 'label' => 'Lokasi acara', 'type' => 'textarea', 'required' => true, 'options' => []],
        ['key' => 'opening_text', 'label' => 'Teks pembuka', 'type' => 'textarea', 'required' => false, 'options' => []],
        ['key' => 'additional_notes', 'label' => 'Catatan tambahan', 'type' => 'textarea', 'required' => false, 'options' => []],
    ];

    protected $fillable = ['name', 'category', 'description', 'fields'];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public static function active(): self
    {
        return self::query()->where('is_active', true)->firstOrFail();
    }
}
