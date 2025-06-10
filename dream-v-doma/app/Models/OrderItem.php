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
        'product_name',
        'quantity',
        'price',
        'total',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * 🔗 Замовлення, до якого належить цей товар
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * 🔗 Продукт, який було замовлено (може бути null)
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
