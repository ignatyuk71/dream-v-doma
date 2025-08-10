<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    protected $fillable = [
        'slug',
        'type',
        'is_filterable',
        'position'
    ];
    public function translations()
    {
        return $this->hasMany(\App\Models\ProductAttributeTranslation::class);
    }
}
