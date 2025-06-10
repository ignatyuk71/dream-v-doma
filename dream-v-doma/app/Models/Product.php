<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'price',
        'quantity_in_stock',
        'status',
        'meta_description'
    ];

    protected $casts = [
        'status' => 'boolean',
        'price' => 'decimal:2'
    ];

    // 🔗 Переклади
    public function translations()
    {
        return $this->hasMany(ProductTranslation::class);
    }

    // 🔗 Всі зображення
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    // 🔗 Варіації
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    // 🔗 Відгуки
    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    // 🔗 Категорії
    public function categories()
    {
        return $this->belongsToMany(Category::class)
            ->using(CategoryProduct::class)
            ->withTimestamps();
    }
}
