<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Enums\OrderStatus;

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
        'status'      => OrderStatus::class, // enum-каст
    ];

    /**
     * Автогенерація номера перед створенням (якщо не передали вручну)
     */
    protected static function booted(): void
    {
        static::creating(function (Order $o) {
            if (empty($o->order_number)) {
                do {
                    $candidate = now()->format('YmdHis') . '-' . Str::upper(Str::random(4));
                } while (self::where('order_number', $candidate)->exists());

                $o->order_number = $candidate;
            }
        });
    }

    /**
     * Перерахунок підсумкової суми з позицій
     */
    public function recalcTotals(): void
    {
        $sum = (float) $this->items()->sum('total');
        $this->total_price = $sum;
        $this->saveQuietly();
    }

    /**
     * 🔗 Клієнт (може бути null — гість)
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
