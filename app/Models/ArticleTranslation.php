<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ArticleTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'locale',
        'title',
        'content',
        'meta_title',
        'meta_description',
    ];

    /**
     * 🔗 Стаття, до якої належить цей переклад
     */
    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
