<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id', // 🔗 варіант
        'product_name',       // snapshot назви
        'variant_sku',        // snapshot артикулу варіанта
        'size',               // snapshot розміру
        'color',              // snapshot кольору
        'image_url',          // snapshot зображення
        'attributes_json',    // інші атрибути (JSON)
        'quantity',
        'price',
        'total',
    ];

    protected $casts = [
        'price'           => 'decimal:2',
        'total'           => 'decimal:2',
        'quantity'        => 'integer',
        'attributes_json' => 'array',
    ];

    /**
     * Замовлення, до якого належить позиція
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Продукт (може бути null, якщо видалений)
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Варіант продукту (може бути null, якщо видалений)
     */
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
