<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * @property-read string|null $icon_url
 */
class ProductColor extends Model
{
    protected $fillable = [
        'product_id',         // Продукт, до якого належить цей набір кольорів
        'linked_product_id',  // Продукт, на який веде цей колір (може бути null)
        'name',
        'url',
        'icon_path',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    protected $appends = [
        'icon_url',
    ];

    /**
     * Основний продукт, до якого належить цей колір (product_id).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Пов’язаний продукт, на який веде цей колір (linked_product_id).
     * Поле може бути null.
     */
    public function linkedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'linked_product_id')->withDefault();
    }

    /**
     * Уніфікований URL іконки (для <img>/background-image):
     * - http(s) залишаємо як є
     * - відносні шляхи нормалізуємо до /storage/...
     */
    public function getIconUrlAttribute(): ?string
    {
        $path = (string) ($this->icon_path ?? '');
        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        // Прибрати leading "/" та "app/public" / "public"
        $p = ltrim($path, '/');
        $p = preg_replace('#^(?:app/)?public/#', '', $p);

        // Якщо вже починається зі storage — не дублюємо
        if (Str::startsWith($p, 'storage/')) {
            return '/' . $p;
        }

        return '/storage/' . $p;
    }

    /**
     * Побудувати ПОВНИЙ (absolute) href для свотча кольору.
     * Пріоритети:
     *  1) Якщо заповнено $this->url:
     *     - http(s) → як є;
     *     - відносний → абсолютний через URL::to()
     *  2) Якщо є linked_product:
     *     - беремо його slug (translations[locale])
     *     - беремо slug однієї з його категорій (translations[locale])
     *     - складаємо "/{locale}/{categorySlug}/{productSlug}"
     *  3) Fallback: поточний товар "/{locale}/{currentCategorySlug}/{currentProductSlug}"
     *
     * @param string $locale
     * @param string $currentCategorySlug  slug категорії поточного товару (для fallback)
     * @param string $currentProductSlug   slug поточного товару (для fallback)
     * @return string Absolute URL
     */
    public function buildHref(string $locale, string $currentCategorySlug, string $currentProductSlug): string
    {
        // 1) Явний URL із БД
        $explicit = trim((string) ($this->url ?? ''));
        if ($explicit !== '') {
            if (Str::startsWith($explicit, ['http://', 'https://'])) {
                return $explicit;
            }
            return URL::to('/' . ltrim($explicit, '/'));
        }

        // 2) Пов’язаний продукт
        if ($this->linked_product_id && $this->relationLoaded('linkedProduct') && $this->linkedProduct) {
            $lp = $this->linkedProduct;

            $lpSlug = optional($lp->translations->firstWhere('locale', $locale))->slug;

            $catSlug = null;
            if ($lp->relationLoaded('categories')) {
                foreach ($lp->categories as $cat) {
                    if ($cat->relationLoaded('translations')) {
                        $tr = $cat->translations->firstWhere('locale', $locale);
                        if ($tr && $tr->slug) {
                            $catSlug = $tr->slug;
                            break;
                        }
                    }
                }
            }

            if ($lpSlug && $catSlug) {
                return URL::to(sprintf('/%s/%s/%s', $locale, $catSlug, $lpSlug));
            }
            // Якщо чогось бракує — падаємо у fallback
        }

        // 3) Fallback: поточний товар
        return URL::to(sprintf('/%s/%s/%s', $locale, $currentCategorySlug, $currentProductSlug));
    }

    /**
     * Стандартне сортування: спочатку дефолтні, потім за назвою.
     */
    public function scopeOrdered($query)
    {
        return $query->orderByDesc('is_default')->orderBy('name');
    }
}
