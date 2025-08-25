<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'delivery_type',
        'np_ref',
        'np_description',
        'np_address',
        'courier_address',
    ];

    /**
     * Відношення до замовлення
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
