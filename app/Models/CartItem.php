<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * 🔗 Корзина, до якої належить цей товар
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * 🔗 Продукт (може бути null, якщо видалено)
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
