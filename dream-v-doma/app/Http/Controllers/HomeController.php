<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Banner;
use App\Models\SpecialOffer;
use Illuminate\Support\Facades\Log;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        $locale = app()->getLocale();
    
        // Тут вибираємо root-категорії або ті, що показуємо на головній
        $categories = Category::where('status', true)
        ->where('show_on_home', true)
        ->whereHas('products') // ← Додаємо цю умову!
        ->orderBy('home_sort_order')
        ->with([
            'translations' => fn($q) => $q->where('locale', $locale),
            'products.images',
            'products.translations' => fn($q) => $q->where('locale', $locale),
        ])
        ->get();
    
        $banners = Banner::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    
        $specialOffers = SpecialOffer::where('is_active', true)
            ->orderBy('sort_order')
            ->take(5)
            ->get();
    
        return view('home', compact('categories', 'banners', 'specialOffers'));
    }
}
