<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'type',
        'size',
        'color',
        'quantity',
        'price_override',
    ];

    protected $casts = [
        'price_override' => 'decimal:2',
    ];

    /**
     * Зв’язок: варіант належить продукту
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
