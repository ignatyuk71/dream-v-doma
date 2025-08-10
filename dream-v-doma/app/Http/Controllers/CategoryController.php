<?php

namespace App\Http\Controllers;

use App\Models\Category;

class CategoryController extends Controller
{
    public function show($locale, $slug)
    {
        app()->setLocale($locale);

        // НЕ фільтруй translations по locale, витягуй ВСІ!
        $category = Category::with('translations')
            ->whereHas('translations', function ($q) use ($locale, $slug) {
                $q->where('locale', $locale)
                ->where('slug', $slug);
            })
            ->firstOrFail();

        // 2. Для активної мови беремо потрібний переклад (для назви, і т.д.)
        $translation = $category->translations->firstWhere('locale', $locale);
        $categorySlug = $translation?->slug ?? $category->id;

        // 3. Продукти категорії — переклади тягни тільки для потрібної мови, картинки теж
        $products = $category->products()
            ->where('status', true)
            ->with(['translations' => function ($q) use ($locale) {
                $q->where('locale', $locale);
            }, 'images'])
            ->get();

        return view('category', [
            'category' => $category,    // ВСІ переклади йдуть у Vue!
            'products' => $products,
            'slug' => $categorySlug,
        ]);
    }


    
}
