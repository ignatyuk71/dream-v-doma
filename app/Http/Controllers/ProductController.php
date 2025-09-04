<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ProductController extends Controller
{
    public function home()
    {
        $locale = app()->getLocale();

        $products = Product::query()
            ->with([
                // головне зображення
                'images' => fn ($q) => $q->where('is_main', 1)
                    ->select(['id','product_id','url','is_main']),

                // переклад продукту поточною мовою
                'translations' => fn ($q) => $q->where('locale', $locale)
                    ->select(['id','product_id','locale','name','slug']),

                // кольори (до 6), порядок: is_default DESC, name ASC
                'colors' => function ($q) {
                    $q->select(['id','product_id','linked_product_id','name','url','icon_path','is_default'])
                      ->orderByDesc('is_default')
                      ->orderBy('name')
                      ->take(6);
                },

                // для побудови повного посилання з кольору:
                // пов’язаний продукт → його переклад → його категорії → їх переклади
                'colors.linkedProduct:id',
                'colors.linkedProduct.translations' => fn ($q) => $q->where('locale', $locale)
                    ->select(['id','product_id','locale','slug']),
                'colors.linkedProduct.categories:id',
                'colors.linkedProduct.categories.translations' => fn ($q) => $q->where('locale', $locale)
                    ->select(['id','category_id','locale','slug']),
            ])
            ->where('status', true)
            ->latest()
            ->limit(12)
            ->get();

        return response()->json($products);
    }

    // SEO-friendly show
    public function show($locale, $categorySlug, $productSlug)
    {
        App::setLocale($locale);

        // 1) Категорія
        $category = Category::whereHas('translations', function ($q) use ($categorySlug, $locale) {
                $q->where('slug', $categorySlug)->where('locale', $locale);
            })
            ->with(['translations' => fn ($q) => $q->where('locale', $locale)])
            ->firstOrFail();

        // 2) Продукт у цій категорії
        $product = Product::whereHas('translations', function ($q) use ($productSlug, $locale) {
                    $q->where('slug', $productSlug)->where('locale', $locale);
                })
                ->whereHas('categories', function ($q) use ($category) {
                    $q->where('categories.id', $category->id);
                })
                ->with([
                    'images',
                    'variants',
                    'translations',

                    // лише схвалені відгуки
                    'reviews' => fn ($q) => $q->where('is_approved', true),

                    // КОЛЬОРИ + усе, що потрібно для абсолютних посилань
                    'colors' => function ($q) {
                        $q->select(['id','product_id','linked_product_id','name','url','icon_path','is_default'])
                          ->orderByDesc('is_default')
                          ->orderBy('name');
                    },
                    'colors.linkedProduct:id',
                    'colors.linkedProduct.translations' => fn ($q) => $q->where('locale', $locale)
                        ->select(['id','product_id','locale','slug']),
                    'colors.linkedProduct.categories:id',
                    'colors.linkedProduct.categories.translations' => fn ($q) => $q->where('locale', $locale)
                        ->select(['id','category_id','locale','slug']),

                    // решта для сторінки продукту
                    'sizeGuide',
                    'categories.translations' => fn ($q) => $q->where('locale', $locale),
                    'attributeValues.translations' => fn ($q) => $q->where('locale', $locale),
                    'attributeValues.attribute.translations' => fn ($q) => $q->where('locale', $locale),
                ])
                ->firstOrFail();

        // 3) Хлібні крихти
        $items = [
            [
                'text'   => __('Головна'),
                'href'   => url("/$locale"),
                'active' => false,
            ],
            [
                'text'   => $category->translations->first()?->name ?? $category->slug,
                'href'   => url("/$locale/" . ($category->translations->first()?->slug ?? $category->slug)),
                'active' => false,
            ],
            [
                'text'   => $product->translations->firstWhere('locale', $locale)?->name ?? $product->slug,
                'href'   => '',
                'active' => true,
            ],
        ];

        return view('product', compact('product', 'items', 'category'));
    }
}
