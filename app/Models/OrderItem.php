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

    /** торкаємо батьківське замовлення при зміні позиції */
    protected $touches = ['order'];

    protected static function booted(): void
    {
        // Перед збереженням — нормалізуємо qty/price і рахуємо total
        static::saving(function (OrderItem $i) {
            $qty = (int)($i->quantity ?? 0);
            if ($qty <= 0) { $qty = 1; }
            $i->quantity = $qty;

            $price = (float)($i->price ?? 0);
            $i->price = round($price, 2);

            $i->total = round($i->price * $i->quantity, 2);
        });

        // Після змін — оновлюємо підсумок замовлення
        static::saved(function (OrderItem $i)  { $i->order?->recalcTotals(); });
        static::deleted(function (OrderItem $i){ $i->order?->recalcTotals(); });
    }

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
