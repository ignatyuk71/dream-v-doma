<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Отримати всі категорії (якщо треба, додай реалізацію)
    public function index(Request $request, string $locale = 'uk')
    {
        // Для адмінки — наприклад, з перекладами:
        $categories = Category::with(['translations', 'children'])->orderBy('id')->get();
        return response()->json($categories);
    }

    public function listAdmin(Request $request)
    {
        $locale = $request->get('locale', app()->getLocale());

        $categories = \App\Models\Category::with([
            'translations' => function($q) use ($locale) {
                $q->where('locale', $locale);
            },
            'children'
        ])->orderBy('id')->get();

        return response()->json($categories);
    }

    public function updateOrder(Request $request)
    {
        foreach ($request->input('order', []) as $id => $sort) {
            Category::where('id', $id)->update(['sort_order' => $sort]);
        }
        return response()->json(['status' => 'ok']);
    }
    
    // Витягує категорії для списку додавання товару
    public function select(Request $request, $locale = 'uk')
    {
        $categories = Category::with(['translations' => function($q) use ($locale) {
            $q->where('locale', $locale);
        }])->orderBy('id')->get();

        $data = $categories->map(function ($cat) use ($locale) {
            $tr = $cat->translations->first();
            return [
                'id' => $cat->id,
                'name' => $tr ? $tr->name : $cat->slug,
            ];
        });

        return response()->json($data);
    }

    // Змінити статус категорії
    public function toggleStatus(Request $request, Category $category)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);
        $category->status = $request->status;
        $category->save();

        return response()->json(['success' => true]);
    }

    // Змінити parent_id категорії
    public function updateParent(Request $request, Category $category)
    {
        $request->validate([
            'parent_id' => 'nullable|exists:categories,id|not_in:' . $category->id,
        ]);
        $category->parent_id = $request->parent_id;
        $category->save();

        return response()->json(['success' => true]);
    }

    // Додай сюди інші потрібні методи (наприклад, show)
}

