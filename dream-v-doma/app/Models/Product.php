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
        'meta_description',
        'size_guide_id', // ✅ додано для mass assignment
        'is_popular'
    ];

    protected $casts = [
        'status' => 'boolean',
        'price' => 'decimal:2',
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

    // 🔗 Всі відгуки
    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    // 🔗 Лише схвалені відгуки
    public function approvedReviews()
    {
        return $this->hasMany(ProductReview::class)
            ->where('is_approved', true);
    }

    // 🔢 Середній рейтинг по схвалених
    public function getAverageRatingAttribute()
    {
        return round($this->approvedReviews()->avg('rating'), 1);
    }

    // 🔗 Категорії
    public function categories()
    {
        return $this->belongsToMany(Category::class)
            ->using(CategoryProduct::class)
            ->withTimestamps();
    }

    // 🔗 Кольори товару
    public function colors()
    {
        return $this->hasMany(ProductColor::class);
    }

    // 🔗 Розмірна сітка
    public function sizeGuide()
    {
        return $this->belongsTo(SizeGuide::class);
    }

    // Product.php
    public function attributeValues()
    {
        return $this->belongsToMany(
            \App\Models\ProductAttributeValue::class,
            'product_attribute_product',
            'product_id',
            'product_attribute_value_id'
        )->with('translations', 'attribute.translations');
    }
}
