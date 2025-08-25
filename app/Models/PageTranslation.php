<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PageTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'locale',
        'title',
        'content',
        'meta_title',
        'meta_description',
    ];

    /**
     * 🔗 Сторінка, до якої належить цей переклад
     */
    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
