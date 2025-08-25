<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialOffer extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'image_path',
        'preview_path',
        'background_path',
        'price',
        'old_price',
        'discount',
        'button_text',
        'button_link',
        'expires_at',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function getRemainingTimeAttribute(): string
    {
        if (!$this->expires_at) return '';
        $diff = $this->expires_at->diff(now());
        return sprintf('%02dd : %02dh : %02dm', $diff->d, $diff->h, $diff->i);
    }

    public function getImageUrlAttribute(): string
    {
        return asset($this->image_path);
    }

    public function getPreviewUrlAttribute(): string
    {
        return asset($this->preview_path);
    }

    public function getBackgroundUrlAttribute(): string
    {
        return asset($this->background_path);
    }
}
