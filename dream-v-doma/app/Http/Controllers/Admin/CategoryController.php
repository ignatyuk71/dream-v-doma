<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('translations')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = Category::with('translations')->get();
        return view('admin.categories.create', compact('parentCategories'));
    }

    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();

        // Беремо назву українською для slug
        $slugSource = $validated['translations']['uk']['name'] ?? 'category';

        $category = Category::create([
            'parent_id' => $validated['parent_id'] ?? null,
            'slug' => \Str::slug($slugSource),
            'status' => $validated['status'] ?? true,
        ]);

        // Зберігаємо всі переклади
        foreach ($validated['translations'] as $translation) {
            $category->translations()->create($translation);
        }

        return redirect()->route('admin.categories.index')->with('success', 'Категорію створено!');
    }

    public function edit(Category $category)
    {
        $parentCategories = Category::where('id', '!=', $category->id)->with('translations')->get();
        $translations = $category->translations->keyBy('locale');

        return view('admin.categories.edit', compact('category', 'parentCategories', 'translations'));
    }
    public function update(StoreCategoryRequest $request, Category $category)
    {
        $validated = $request->validated();
    
        $slugSource = $validated['translations']['uk']['name'] ?? 'category';
    
        $category->update([
            'parent_id' => $validated['parent_id'] ?? null,
            'slug' => \Str::slug($slugSource),
            'status' => $validated['status'] ?? true,
        ]);
    
        // Видалимо старі переклади
        $category->translations()->delete();
    
        // Додаємо нові
        foreach ($validated['translations'] as $translation) {
            $category->translations()->create($translation);
        }
    
        return redirect()->route('admin.categories.index')->with('success', 'Категорію оновлено!');
    }
    
    public function destroy(Category $category)
    {
        // Видаляємо переклади лише цієї категорії
        $category->translations()->delete();
    
        // Видаляємо саму категорію
        $category->delete();
    
        return redirect()->route('admin.categories.index')->with('success', 'Категорію видалено!');
    }
    

    
}
