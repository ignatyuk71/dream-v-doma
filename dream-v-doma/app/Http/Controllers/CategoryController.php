<?php

namespace App\Http\Controllers;

use App\Models\Category;

class CategoryController extends Controller
{
    public function show($locale, $slug)
    {
        app()->setLocale($locale);

        $category = Category::with([
                'translations',
                'products.images',
                'products.translations',
            ])
            ->whereHas('translations', fn($q) => $q->where('slug', $slug))
            ->firstOrFail();

        // Якщо це JSON-запит (через axios/fetch)
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($category);
        }

        // Інакше — Blade
        return view('category', compact('category'));
    }

}
