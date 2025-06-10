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
        'meta_title',
        'meta_description',
    ];

    /**
     * Зв’язок: переклад належить категорії
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
