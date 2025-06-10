<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\ProductTranslation;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with([
            'images', // ← це головне
            'translations' => fn($q) => $q->where('locale', 'ua'),
            'variants',
        ])->get();

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::with('translations')->get()->map(function ($category) {
            $ukTranslation = $category->translations->where('locale', 'uk')->first();

            return [
                'id' => $category->id,
                'name' => $ukTranslation?->name ?? 'Без назви',
            ];
        });

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $translations = $request->input('translations', []);
        $variants = $request->input('variants', []);
    
        $request->validate([
            'price' => 'required|numeric',
            'status' => 'required|boolean',
            'sku' => 'required|string|max:255|unique:products,sku',
        ]);
    
        $sku = $request->sku ?: 'SKU-' . strtoupper(Str::random(8));
    
        $product = Product::create([
            'sku' => $sku,
            'price' => $request->price,
            'status' => $request->status,
        ]);
    
        // Категорії
        if ($request->filled('category_ids') && is_array($request->category_ids)) {
            $categoryIds = array_filter($request->category_ids, fn($id) => is_numeric($id));
            $product->categories()->sync($categoryIds);
        }
    
        // Зображення з кастомною назвою
        if ($request->hasFile('images')) {
            $baseName = Str::slug($translations[0]['name'] ?? 'product');
            $date = now()->format('Y-m-d');
            $isMains = $request->input('is_main', []); // 🔥 обовʼязково
        
            foreach ($request->file('images') as $index => $file) {
                $ext = $file->getClientOriginalExtension();
                $filename = "{$baseName}-dream-v-doma-{$date}-" . ($index + 1) . ".{$ext}";
                $path = $file->storeAs('uploads/products', $filename, 'public');
        
                ProductImage::create([
                    'product_id' => $product->id,
                    'url' => $path,
                    'position' => $request->positions[$index] ?? $index,
                    'title' => $request->titles[$index] ?? '',
                    'alt' => $request->alts[$index] ?? '',
                    'is_main' => isset($isMains[$index]) && $isMains[$index] ? 1 : 0
                ]);
            }
        }
        
    
        // Переклади
        if (!empty($translations)) {
            foreach ($translations as $t) {
                ProductTranslation::create([
                    'product_id' => $product->id,
                    'locale' => $t['locale'],
                    'name' => $t['name'],
                    'meta_title' => $t['meta_title'] ?? '',
                    'meta_description' => $t['meta_description'] ?? '',
                    'description' => $t['description'] ?? '',
                    'slug' => isset($t['slug']) && trim($t['slug']) !== ''
                        ? Str::slug($t['slug'])
                        : Str::slug($t['name'] ?? ''),
                ]);
            }
        }
    
        // Варіанти
        if (!empty($variants)) {
            foreach ($variants as $v) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'size' => $v['size'],
                    'color' => $v['color'],
                    'price_override' => $v['price'],
                    'quantity' => $v['quantity'],
                ]);
            }
        }
    
        return redirect()
        ->route('admin.products.index')
        ->with('success', '✅ Продукт успішно створено!');

    }


    public function edit(Product $product)
    {
        $product->load([
            'translations',
            'variants',
            'images',
            'categories.translations'
        ]);

        // Додаємо name до вибраних категорій
        $product->categories->transform(function ($cat) {
            $cat->name = $cat->translations->first()?->name ?? '---';
            return $cat;
        });

        // Список всіх категорій з name
        $categories = \App\Models\Category::with('translations')->get()->map(function ($cat) {
            $cat->name = $cat->translations->first()?->name ?? '---';
            return $cat;
        });

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $translations = $request->input('translations', []);
        $variants = $request->input('variants', []);

        $request->validate([
            'price' => 'required|numeric',
            'status' => 'required|boolean',
            'sku' => 'required|string|max:255|unique:products,sku,' . $product->id,
        ]);

        $product->update([
            'sku' => $request->sku,
            'price' => $request->price,
            'status' => $request->status,
        ]);

        // Категорії
        $categoryIds = array_filter($request->input('category_ids', []), fn($id) => is_numeric($id));
        $product->categories()->sync($categoryIds);

        // Зображення
        $existingImages = $request->input('existing_images', []);
        $product->images()->whereNotIn('id', $existingImages)->delete();

        foreach ($existingImages as $index => $imageId) {
            $img = ProductImage::find($imageId);
            if ($img) {
                $img->update([
                    'title' => $request->titles[$index] ?? '',
                    'alt' => $request->alts[$index] ?? '',
                    'position' => $request->positions[$index] ?? $index,
                    'is_main' => isset($request->is_main[$index]) && $request->is_main[$index] ? 1 : 0,
                ]);
            }
        }

        if ($request->hasFile('images')) {
            $baseName = Str::slug($translations[0]['name'] ?? 'product');
            $date = now()->format('Y-m-d');
            foreach ($request->file('images') as $index => $file) {
                $ext = $file->getClientOriginalExtension();
                $filename = "{$baseName}-dream-v-doma-{$date}-" . uniqid() . ".{$ext}";
                $path = $file->storeAs('uploads/products', $filename, 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'url' => $path,
                    'position' => $request->positions[$index] ?? $index,
                    'title' => $request->titles[$index] ?? '',
                    'alt' => $request->alts[$index] ?? '',
                    'is_main' => isset($request->is_main[$index]) && $request->is_main[$index] ? 1 : 0,
                ]);
            }
        }

        // Переклади
        ProductTranslation::where('product_id', $product->id)->delete();
        foreach ($translations as $t) {
            ProductTranslation::create([
                'product_id' => $product->id,
                'locale' => $t['locale'],
                'name' => $t['name'],
                'meta_title' => $t['meta_title'] ?? '',
                'meta_description' => $t['meta_description'] ?? '',
                'description' => $t['description'] ?? '',
                'slug' => isset($t['slug']) && trim($t['slug']) !== ''
                    ? Str::slug($t['slug'])
                    : Str::slug($t['name'] ?? ''),
            ]);
        }

        // Варіанти
        ProductVariant::where('product_id', $product->id)->delete();
        foreach ($variants as $v) {
            ProductVariant::create([
                'product_id' => $product->id,
                'size' => $v['size'],
                'color' => $v['color'],
                'price_override' => $v['price'],
                'quantity' => $v['quantity'],
            ]);
        }

        return redirect()
    ->route('admin.products.index')
    ->with('success', '✅ Продукт успішно оновлено!');


    }


    public function destroy(Product $product)
    {
        // Видалення повʼязаних зображень з диску та БД
        foreach ($product->images as $image) {
            if ($image->url && \Storage::disk('public')->exists($image->url)) {
                \Storage::disk('public')->delete($image->url);
            }
            $image->delete();
        }
    
        // Видалення перекладів
        $product->translations()->delete();
    
        // Видалення варіантів
        $product->variants()->delete();
    
        // Очистка звʼязку з категоріями
        $product->categories()->detach();
    
        // Видалення самого продукту
        $product->delete();
    
        return redirect()->route('admin.products.index');
    }
    
    
    

}
