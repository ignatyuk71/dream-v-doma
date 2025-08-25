<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CategoryProduct extends Pivot
{
    protected $table = 'category_product';

    protected $fillable = [
        'product_id',
        'category_id',
    ];

    public $timestamps = true;
}
