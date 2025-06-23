<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'order_number',
        'total_price',
        'currency',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
    ];

    /**
     * 🔗 Клієнт, який зробив замовлення (може бути null — гість)
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * 🔗 Товари в замовленні
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * 🔗 Доставка замовлення
     */
    public function delivery()
    {
        return $this->hasOne(OrderDelivery::class);
    }
}
