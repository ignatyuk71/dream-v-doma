<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductColor extends Model
{
    protected $fillable = [
        'product_id',         // Продукт, до якого належить цей набір кольорів
        'linked_product_id',  // Продукт, на який веде цей колір (може бути null)
        'name',
        'url',
        'icon_path',
        'is_default',
    ];

    /**
     * Основний продукт, до якого належить цей колір (product_id)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Пов’язаний продукт, на який веде цей колір (linked_product_id)
     * Поле може бути null!
     */
    public function linkedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'linked_product_id')->withDefault();
    }
}
