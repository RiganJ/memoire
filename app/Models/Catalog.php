<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Catalog extends Model
{
    protected $fillable = [
        'name',
        'category',
        'package',
        'color',
        'link',
        'image_path',
        'status',
    ];

    public function orderFormTemplate(): HasOne
    {
        return $this->hasOne(OrderFormTemplate::class);
    }
}
