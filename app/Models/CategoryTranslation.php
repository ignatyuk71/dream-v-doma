<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoryTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'locale',
        'name',
        'description', // ← Додаємо опис
        'meta_title',
        'meta_description',
        'slug',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}

