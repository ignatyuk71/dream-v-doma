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


        $locale  = app()->getLocale();          // 'uk' або 'ru'
        $locales = ['uk', 'ru'];                // тягнемо обидві мови для характеристик

        $categories = Category::where('status', true)
            ->where('show_on_home', true)
            ->whereHas('products')
            ->orderBy('home_sort_order')
            ->with([
                // Категорія / продукт — лише поточна мова
                'translations'            => fn ($q) => $q->where('locale', $locale),
                'products.images'         => fn ($q) => $q->where('is_main', 1),
                'products.translations'   => fn ($q) => $q->where('locale', $locale),

                // Рейтинг / кількість відгуків
                'products' => fn ($q) =>
                    $q->withCount(['approvedReviews as reviews_count'])
                    ->withAvg('approvedReviews as avg_rating', 'rating'),
            ])
            ->get();
    
        $banners = Banner::where('is_active', true)->orderBy('sort_order')->get();
        $specialOffers = SpecialOffer::where('is_active', true)->orderBy('sort_order')->take(5)->get();
    
        return view('home', compact('categories', 'banners', 'specialOffers'));
    }
    
    

    
    
}