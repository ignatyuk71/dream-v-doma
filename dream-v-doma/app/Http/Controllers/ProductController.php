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
        $products = Product::with([
            'images' => fn($q) => $q->where('is_main', 1),
            'translations' => fn($q) => $q->where('locale', $locale),
        ])
        ->where('status', true)
        ->latest()
        ->limit(12)
        ->get();

        return response()->json($products);
    }

    // ОНОВЛЕНИЙ SEO-friendly show
    public function show($locale, $categorySlug, $productSlug)
    {
        App::setLocale($locale);

        // 1. Знаходимо категорію по slug і мові
        $category = Category::whereHas('translations', function ($q) use ($categorySlug, $locale) {
            $q->where('slug', $categorySlug)->where('locale', $locale);
        })
        ->with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }])
        ->firstOrFail();

        // 2. Знаходимо продукт по slug і мові, який належить цій категорії
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
                'reviews' => fn($q) => $q->where('is_approved', true),
                'colors.linkedProduct.translations',
                'sizeGuide',
                'categories.translations',
                'attributeValues.translations',
                'attributeValues.attribute.translations',
            ])
            ->firstOrFail();

        // 3. Breadcrumbs
        $items = [
            [
                'text' => __('Головна'),
                'href' => url("/$locale"),
                'active' => false,
            ],
            [
                'text' => $category->translations->first()?->name ?? $category->slug,
                'href' => url("/$locale/" . ($category->translations->first()?->slug ?? $category->slug)),
                'active' => false,
            ],
            [
                'text' => $product->translations->firstWhere('locale', $locale)?->name ?? $product->slug,
                'href' => '',
                'active' => true,
            ],
        ];

        return view('product', compact('product', 'items', 'category'));
    }
}
