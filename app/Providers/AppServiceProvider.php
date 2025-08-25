<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Визначення локалі з URL
        $locale = Request::segment(1);
        if (in_array($locale, ['uk', 'ru'])) {
            App::setLocale($locale);
        }

        // View::composer для глобального меню
        View::composer('components.navbar', function ($view) use ($locale) {
            $categories = Category::with(['translations', 'children.translations'])
                ->whereNull('parent_id')
                ->where('status', true)
                ->get()
                ->map(function ($category) use ($locale) {
                    $translation = $category->translations->firstWhere('locale', $locale);
                    return [
                        'id' => $category->id,
                        'slug' => $translation?->slug ?? $category->slug,
                        'name' => $translation?->name ?? $category->slug,
                        'children' => $category->children->map(function ($child) use ($locale) {
                            $childTranslation = $child->translations->firstWhere('locale', $locale);
                            return [
                                'id' => $child->id,
                                'slug' => $childTranslation?->slug ?? $child->slug,
                                'name' => $childTranslation?->name ?? $child->slug,
                            ];
                        }),
                    ];
                });

            $view->with('menuCategories', $categories);
        });
    }
}
