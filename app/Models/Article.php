<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * 🔗 Переклади статті (uk, en, тощо)
     */
    public function translations()
    {
        return $this->hasMany(ArticleTranslation::class);
    }
}
