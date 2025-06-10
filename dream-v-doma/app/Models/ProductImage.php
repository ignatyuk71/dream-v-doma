<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'url',
        'is_main',
        'position',
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'position' => 'integer',
    ];

    protected $appends = ['full_url'];

    /**
     * Зв’язок: зображення належить продукту
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Повний URL до зображення
     */
    public function getFullUrlAttribute(): string
    {
        return Storage::url($this->url);
    }
}