<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderFormTemplate extends Model
{
    protected $fillable = ['name', 'category', 'description', 'fields'];

    protected function casts(): array
    {
        return ['fields' => 'array'];
    }
}
