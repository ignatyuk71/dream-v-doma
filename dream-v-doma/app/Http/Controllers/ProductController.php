<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App; // ← ДОДАЙ ЦЕ

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

    public function show($locale, $slug)
    {
        App::setLocale($locale);

        $product = Product::with([
            'images',
            'variants',
            'translations', // ← витягуємо всі переклади!
            'reviews',
            'categories.translations' => fn($q) => $q->where('locale', $locale),
        ])
        ->whereHas('translations', fn($q) => $q->where('slug', $slug)->where('locale', $locale))
        ->firstOrFail();

        return view('product', compact('product'));
    }
}
