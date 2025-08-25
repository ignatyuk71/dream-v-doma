<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'session_id',
    ];

    /**
     * 🔗 Клієнт (може бути null для гостьових корзин)
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * 🔗 Товари в корзині
     */
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
}
