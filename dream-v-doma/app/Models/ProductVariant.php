<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'type',
        'size',
        'color',
        'quantity',
        'price_override',
        'old_price',
    ];

    protected $casts = [
        'price_override' => 'decimal:2',
        'old_price'      => 'decimal:2',
        'quantity'       => 'integer',
    ];

    /**
     * Зв’язок: варіант належить продукту
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
