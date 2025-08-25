<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_blocked',
    ];

    protected $casts = [
        'is_blocked' => 'boolean',
    ];

    /**
     * Зв’язок: клієнт має багато адрес
     */
    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }
    public function address()
    {
        return $this->hasOne(CustomerAddress::class)->latestOfMany();
    }

    /**
     * Якщо в майбутньому буде авторизація — додай implements Authenticatable + hash
     */
}
