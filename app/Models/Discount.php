<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'amount',
        'percentage',
        'active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'integer',
        'active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * 🔁 Можна додати логіку:
     * чи діє зараз знижка
     */
    public function isActiveNow(): bool
    {
        $now = now();
        return $this->active &&
            (!$this->starts_at || $now >= $this->starts_at) &&
            (!$this->ends_at || $now <= $this->ends_at);
    }
}
