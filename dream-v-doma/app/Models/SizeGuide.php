<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SizeGuide extends Model
{
    protected $fillable = [
        'slug',
        'name_uk',
        'name_ru',
        'data',
    ];

    protected $casts = [
        'data' => 'array', // автоматично конвертує JSON <-> array
    ];
}
