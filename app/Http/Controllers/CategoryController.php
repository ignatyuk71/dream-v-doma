<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ProductVariant;

class CategoryController extends Controller
{
    /** Роздільник списків у красивому URL */
    private const LIST_SEP = '_';

    public function show(string $locale, string $category, ?string $filters = null)
    {
        app()->setLocale($locale);

        // 1) Категорія
        $categoryModel = Category::with('translations')
            ->whereHas('translations', function ($q) use ($locale, $category) {
                $q->where('locale', $locale)->where('slug', $category);
            })
            ->firstOrFail();

        $translation  = $categoryModel->translations->firstWhere('locale', $locale);
        $categorySlug = $translation?->slug ?? (string)$categoryModel->id;

        // 2) Якщо прийшли фільтри у QUERY → редіректимо на красивий URL
        if ($this->hasQueryFilters()) {
            $qFilters = $this->filtersFromQuery();
            $pretty   = $this->buildFiltersPath($qFilters);

            return redirect()->to(
                $pretty
                    ? route('category.filtered', ['locale' => $locale, 'category' => $categorySlug, 'filters' => $pretty])
                    : route('category.show',     ['locale' => $locale, 'category' => $categorySlug])
            );
        }

        // 3) Фільтри зі шляху (приймаємо '_' або ',' як роздільники списків)
        $filtersArr = $this->filtersFromPath($filters);

        // 4) Ідентифікатори товарів
        $productIds = $categoryModel->products()->pluck('products.id');

        // 5) Фасети (унікальні значення)
        $facetSizes = ProductVariant::whereIn('product_id', $productIds)
            ->whereNotNull('size')
            ->select('size')->distinct()
            ->orderByRaw('CAST(SUBSTRING_INDEX(size, "-", 1) AS UNSIGNED), size')
            ->pluck('size');

        $facetColors = ProductVariant::whereIn('product_id', $productIds)
            ->whereNotNull('color')
            ->select('color')->distinct()
            ->orderBy('color')
            ->pluck('color');

        // 6) Мін/макс ціни (COALESCE override/baseline)
        $priceAgg = ProductVariant::from('product_variants as pv')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->whereIn('pv.product_id', $productIds)
            ->selectRaw('MIN(COALESCE(pv.price_override, p.price)) as min_price,
                         MAX(COALESCE(pv.price_override, p.price)) as max_price')
            ->first();

        $priceRange = [
            'min' => (float)($priceAgg->min_price ?? 0),
            'max' => (float)($priceAgg->max_price ?? 0),
        ];

        // 7) Валідація + нормалізація
        $validSizes  = array_values(array_intersect($filtersArr['sizes'] ?? [], $facetSizes->toArray()));
        $validColors = array_values(array_intersect($filtersArr['colors'] ?? [], $facetColors->toArray()));

        $minPrice = $filtersArr['min_price'] ?? $priceRange['min'];
        $maxPrice = $filtersArr['max_price'] ?? $priceRange['max'];

        $minPrice = max($priceRange['min'], (float)$minPrice);
        $maxPrice = min($priceRange['max'], (float)$maxPrice);
        if ($minPrice > $maxPrice) {
            $minPrice = $priceRange['min'];
            $maxPrice = $priceRange['max'];
        }

        // Якщо ціновий фільтр нічого не звужує — не додаємо його до URL
        $dropPrice = (
            ((int)$minPrice === (int)$priceRange['min'] && (int)$maxPrice === (int)$priceRange['max']) ||
            ((int)$minPrice === 0 && (int)$maxPrice === 0) ||
            ((int)$priceRange['min'] === (int)$priceRange['max'])
        );
        if ($dropPrice) {
            $minPrice = null;
            $maxPrice = null;
        }

        // 7.1) Побудова нормалізованого шляху (зі сплітером "_")
        $normalized = [
            'sizes'     => $validSizes,
            'colors'    => $validColors,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
        ];
        $normalizedPath = $this->buildFiltersPath($normalized);
        $currentPath    = $filters ?? '';

        // 7.2) Якщо відрізняється — 301 на красивий URL (але не для AJAX)
        if ($normalizedPath !== $currentPath && !(request()->ajax() || request()->header('X-Partial'))) {
            return redirect()
                ->to(
                    $normalizedPath
                        ? route('category.filtered', ['locale' => $locale, 'category' => $categorySlug, 'filters' => $normalizedPath])
                        : route('category.show',     ['locale' => $locale, 'category' => $categorySlug])
                )
                ->setStatusCode(301);
        }

        // 8) Продукти з урахуванням фільтрів
        $productsQuery = $categoryModel->products()
            ->where('status', true)
            ->with([
                'translations' => fn($q) => $q->where('locale', $locale),
                'images'       => fn($q) => $q->orderByDesc('is_main')->orderBy('position')->orderBy('id'),
            ])
            ->withAvg('approvedReviews as avg_rating', 'rating')
            ->withCount('approvedReviews as reviews_count');

        if (!empty($validSizes)) {
            $productsQuery->whereHas('variants', fn($q) => $q->whereIn('size', $validSizes));
        }
        if (!empty($validColors)) {
            $productsQuery->whereHas('variants', fn($q) => $q->whereIn('color', $validColors));
        }
        if ($minPrice !== null && $maxPrice !== null) {
            $productsQuery->where(function ($q) use ($minPrice, $maxPrice) {
                $q->whereBetween('price', [$minPrice, $maxPrice])
                  ->orWhereHas('variants', fn($v) => $v->whereBetween('price_override', [$minPrice, $maxPrice]));
            });
        }

        $products = $productsQuery
            ->paginate(30);

        // 9) Пагінація — базовий шлях із нормалізованим filters
        $basePath = $normalizedPath
            ? route('category.filtered', ['locale' => $locale, 'category' => $categorySlug, 'filters' => $normalizedPath])
            : route('category.show',     ['locale' => $locale, 'category' => $categorySlug]);

        $products->withPath($basePath);
        $products->appends(request()->query()); // зберігаємо інші GET-параметри (сортування тощо)

        // 10) Хлібні крихти + опис
        $items = [
            ['text' => __('Головна'), 'href' => '/' . app()->getLocale(), 'active' => false],
            ['text' => $translation?->name ?? $categoryModel->slug, 'href' => '', 'active' => true],
        ];

        $categoryBlocks = [];
        $raw = $translation?->description;
        if (!is_null($raw)) {
            if (is_string($raw)) {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE) $raw = $decoded;
            }
            if (is_array($raw)) {
                $value = array_is_list($raw) ? $raw : ($raw[$locale] ?? reset($raw) ?? null);
                if (is_array($value)) {
                    $categoryBlocks = $value;
                } elseif (is_string($value) && trim($value) !== '') {
                    $categoryBlocks = [[ 'type' => 'text', 'title' => null, 'text' => $value ]];
                }
            } elseif (is_string($raw) && trim($raw) !== '') {
                $categoryBlocks = [[ 'type' => 'text', 'title' => null, 'text' => $raw ]];
            }
        }

        // 11) Дані для view/partials
        $viewData = [
            'category'       => $categoryModel,
            'translation'    => $translation,
            'slug'           => $categorySlug,
            'products'       => $products,
            'items'          => $items,
            'categoryBlocks' => $categoryBlocks,
            'filters'        => [
                'sizes'     => $validSizes,
                'colors'    => $validColors,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
            ],
            'facets'         => [
                'sizes'  => $facetSizes,
                'colors' => $facetColors,
            ],
            'priceRange'     => $priceRange,
        ];

        // 12) Якщо це AJAX/partial — повертаємо лише потрібні шматки HTML
        if (request()->ajax() || request()->header('X-Partial')) {
            return response()->json([
                'chips'      => view('home.category.filters-active', $viewData)->render(),
                'products'   => view('home.category.products-grid', $viewData)->render(),
                'pagination' => view('home.category.pagination', $viewData)->render(),
            ]);
        }

        // 13) Повне рендерення сторінки
        return view('category', $viewData);
    }

    /* ================= helpers ================= */

    private function hasQueryFilters(): bool
    {
        return request()->hasAny(['sizes','colors','min_price','max_price']);
    }

    private function filtersFromQuery(): array
    {
        return [
            'sizes'     => array_filter((array)request('sizes', [])),
            'colors'    => array_filter((array)request('colors', [])),
            'min_price' => request()->filled('min_price') ? (float)request('min_price') : null,
            'max_price' => request()->filled('max_price') ? (float)request('max_price') : null,
        ];
    }

    /**
     * Розбираємо сегменти шляху виду:
     *   rozmir-33-34_36-37
     *   kolir-black_white
     *   tsina-250-400
     * Підтримуємо також старий формат із комами (для сумісності) — роз’єднувач списків [,_]
     */
    private function filtersFromPath(?string $path): array
    {
        $res = ['sizes'=>[], 'colors'=>[], 'min_price'=>null, 'max_price'=>null];
        if (!$path) return $res;

        foreach (explode('/', trim($path, '/')) as $seg) {
            if (preg_match('/^rozmir-(.+)$/u', $seg, $m)) {
                $res['sizes']  = array_values(array_filter(preg_split('/[,_]/', $m[1])));
            } elseif (preg_match('/^kolir-(.+)$/u', $seg, $m)) {
                $res['colors'] = array_values(array_filter(preg_split('/[,_]/', $m[1])));
            } elseif (preg_match('/^tsina-(\d+)-(\d+)$/u', $seg, $m)) {
                $res['min_price'] = (float)$m[1];
                $res['max_price'] = (float)$m[2];
            }
        }
        return $res;
    }

    /** Будуємо красивий шлях у фіксованому порядку: rozmir → kolir → tsina */
    private function buildFiltersPath(array $filters): string
    {
        $parts = [];
        if (!empty($filters['sizes']))  $parts[] = 'rozmir-'.implode(self::LIST_SEP, $filters['sizes']);
        if (!empty($filters['colors'])) $parts[] = 'kolir-'.implode(self::LIST_SEP, $filters['colors']);
        if ($filters['min_price'] !== null && $filters['max_price'] !== null) {
            $parts[] = 'tsina-'.(int)$filters['min_price'].'-'.(int)$filters['max_price'];
        }
        return implode('/', $parts);
    }
}
