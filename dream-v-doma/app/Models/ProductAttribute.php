<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    protected $fillable = [
        'slug',
        'type',
        'is_filterable',
        'position',
    ];

    protected $casts = [
        'is_filterable' => 'boolean',
        'position'      => 'integer',
    ];

    /** Переклади атрибуту */
    public function translations()
    {
        return $this->hasMany(\App\Models\ProductAttributeTranslation::class);
    }

    /** Значення (value) цього атрибуту */
    public function values()
    {
        return $this->hasMany(\App\Models\ProductAttributeValue::class, 'product_attribute_id');
    }

    /** Показувати лише фільтрувальні атрибути */
    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }

    /** Сортування за позицією */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    /**
     * Отримати назву атрибуту активною мовою (fallback на slug)
     */
    public function getLocalizedName(string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        $name = $this->translations
            ->firstWhere('locale', $locale)
            ->name ?? null;

        return $name ?: ucfirst($this->slug);
    }
}
