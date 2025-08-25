<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Page extends Model
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
     * 🔗 Переклади сторінки (uk, en, тощо)
     */
    public function translations()
    {
        return $this->hasMany(PageTranslation::class);
    }
}
