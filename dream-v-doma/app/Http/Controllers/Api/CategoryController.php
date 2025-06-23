<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    public function index(Request $request, string $locale)
{
    app()->setLocale($locale);

    $categories = Category::with(['translations', 'children.translations'])
        ->whereNull('parent_id')
        ->where('status', true)
        ->orderBy('id')
        ->get()
        ->map(function ($category) use ($locale) {
            $translatedCategory = $category->translations->firstWhere('locale', $locale);
            return [
                'id' => $category->id,
                'name' => $translatedCategory?->name ?? $category->slug,
                'slug' => $translatedCategory?->slug ?? $category->slug,
                'children' => $category->children
                    ->where('status', true)
                    ->map(function ($child) use ($locale) {
                        $translatedChild = $child->translations->firstWhere('locale', $locale);
                        return [
                            'id' => $child->id,
                            'name' => $translatedChild?->name ?? $child->slug,
                            'slug' => $translatedChild?->slug ?? $child->slug,
                        ];
                    })->values(),
            ];
        });

    return response()->json($categories);
}


}
