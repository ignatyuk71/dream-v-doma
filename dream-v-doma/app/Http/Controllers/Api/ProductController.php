<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


class ProductController extends Controller
{
    public function list(Request $request)
    {
        $locale = $request->get('locale', 'uk');
    
        $query = \App\Models\Product::query()
            ->with([
                'translations' => fn($q) => $q->where('locale', $locale),
                'categories.translations' => fn($q) => $q->where('locale', $locale),
                'images'
            ]);
    
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('translations', function ($t) use ($search) {
                    $t->where('name', 'like', "%$search%");
                })->orWhere('sku', 'like', "%$search%");
            });
        }
    
        if ($request->filled('status') && in_array($request->status, ['0', '1'])) {
            $query->where('status', $request->status);
        }
    
        if ($request->filled('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category);
            });
        }
    
        $perPage = $request->get('per_page', 20);   // тут вказується кількисть позицій продукта
        $products = $query->paginate($perPage);
    
        $result = $products->getCollection()->map(function ($product) use ($locale) {
            $translation = $product->translations->first();
            $category = $product->categories->first();
            $categoryName = $category?->translations->first()?->name ?? '—';
    
            // Визначаємо головне зображення (is_main = true)
            $mainImage = $product->images
                ->where('is_main', true)
                ->sortBy('position')
                ->first();
    
            if (!$mainImage) {
                $mainImage = $product->images->sortBy('position')->first();
            }
    
            $image = $mainImage?->full_url ?? $mainImage?->url ?? '/assets/img/placeholder.svg';
    
            return [
                'id' => $product->id,
                'name' => $translation?->name ?? '',
                'sku' => $product->sku,
                'price' => $product->price,
                'qty' => $product->quantity_in_stock,
                'status' => $product->status,
                'category_name' => $categoryName,
                'image' => $image,
            ];
        });
    
        return response()->json([
            'data' => $result,
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
        ]);
    }
    


    public function destroy(Product $product)
    {
        DB::beginTransaction();
        try {
            \Log::info('Видалення продукту', ['id' => $product->id]);
    
            // Видалити всі кольори, де цей продукт є як linked_product_id
            \App\Models\ProductColor::where('linked_product_id', $product->id)->delete();
    
            // Видалення зображень (БД і файлів)
            if ($product->images) {
                foreach ($product->images as $image) {
                    $filePath = ltrim($image->path, '/');
                    if ($filePath) {
                        Storage::disk('public')->delete($filePath);
                    }
                    $image->delete();
                }
            }
    
            // Видалення папок
            $folders = [
                "products/{$product->id}",
                "colors/{$product->id}",
                "images/description/{$product->id}",
            ];
            foreach ($folders as $folder) {
                if (Storage::disk('public')->exists($folder)) {
                    Storage::disk('public')->deleteDirectory($folder);
                }
            }            
    
            // Видалення зв'язків з кольорами (hasMany)
            $product->colors()->delete();
    
            // Видалення зв'язків з атрибутами, якщо потрібно
            $product->attributeValues()->detach();
    
            // Видалення самого продукту
            $product->delete();
    
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Товар видалено']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Помилка при видаленні продукту', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'product_id' => $product->id ?? null
            ]);
            return response()->json(['error' => 'Помилка при видаленні товару'], 500);
        }
    }    
    

    public function toggleStatus(Product $product, Request $request)
    {
        $status = $request->input('status');

        if (!in_array($status, [0, 1])) {
            return response()->json(['error' => 'Invalid status'], 422);
        }

        $product->status = $status;
        $product->save();

        return response()->json(['success' => true, 'status' => $product->status]);
    }

    
    // Метод для отримання всіх категорій
    public function categories()
    {
        return response()->json(\App\Models\Category::all());
    }

    // Метод для отримання одного продукту за мовою і slug (перекладом)
    public function show($locale, $slug)
    {
        $product = Product::with([
            'images', // всі зображення
            'variants', // варіації продукту
            'translations' => fn($q) => $q->where('locale', $locale), // переклади продукту по мові
            'reviews', // всі відгуки
            'categories.translations' => fn($q) => $q->where('locale', $locale), // переклади категорій по мові
        ])
        // Фільтруємо продукт за slug і мовою перекладу
        ->whereHas('translations', fn($q) => $q->where('slug', $slug)->where('locale', $locale))
        ->firstOrFail();

        // Повертаємо JSON з продуктом
        return response()->json($product);
    }

    // app/Http/Controllers/Api/ProductController.php

    public function getLinkedInfo(Request $request)
    {
        $ids = $request->input('ids', []);
        $locale = app()->getLocale(); // або $request->input('locale', 'uk');

        $products = \App\Models\Product::whereIn('id', $ids)
            ->with(['translations' => function($q) use ($locale) {
                $q->where('locale', $locale);
            }])
            ->get();

        $result = [];
        foreach ($products as $product) {
            $translation = $product->translations->first();
            $result[$product->id] = [
                'sku' => $product->sku,
                'name' => $translation ? $translation->name : '',
                'slug' => $translation ? $translation->slug : '',
            ];
        }
        return response()->json($result);
    }

}
