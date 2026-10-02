<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'catalog_id',
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

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function catalog()
    {
        return $this->belongsTo(Catalog::class);
    }
}
