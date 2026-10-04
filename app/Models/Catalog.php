<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
