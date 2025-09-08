<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Models\ProductColor;
use Illuminate\Support\Facades\DB;
use App\Models\ProductTranslation;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Вивід списку всіх продуктів (admin).
     */
    public function index()
    {
        // Підтягуємо переклади, картинки і варіанти для таблиці продуктів
        $products = Product::with([
            'images',
            'translations' => fn($q) => $q->where('locale', 'uk'),
            'variants',
        ])->get();

        return view('admin.products.index', compact('products'));
    }

    /**
     * Відкриття форми редагування продукту (admin)
     */
    public function edit(Product $product)
    {
        $product->load([
            'categories.translations',
            'images',
            'variants',
            'attributeValues.attribute.translations',
            'attributeValues.translations',
            'colors',
            'translations',
        ]);
    
        $categories = \App\Models\Category::with('translations')->get();
    
        // Формуємо масив характеристик для форми
        $attributes = [
            'uk' => [],
            'ru' => [],
        ];
        foreach ($product->attributeValues as $value) {
            $attr = $value->attribute;
            foreach (['uk', 'ru'] as $locale) {
                $attrName = $attr->translations->where('locale', $locale)->first()?->name ?? '';
                $valueName = $value->translations->where('locale', $locale)->first()?->value ?? '';
                if ($attrName && $valueName) {
                    $attributes[$locale][] = [
                        'name' => $attrName,
                        'value' => $valueName,
                    ];
                }
            }
        }
    
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'product'    => $product,
                'categories' => $categories,
                'attributes' => $attributes, // <- ДОДАЙ attributes!
            ]);
        }
    
        return view('admin.products.edit', compact('product', 'categories', 'attributes'));
    }
    
    
    

    /**
     * Відкриття форми створення нового продукту (admin)
     */
    public function create()
    {
        // Всі категорії з українськими назвами
        $categories = Category::with('translations')->get()->map(function ($category) {
            $ukTranslation = $category->translations->where('locale', 'uk')->first();
            return [
                'id' => $category->id,
                'name' => $ukTranslation?->name ?? 'Без назви',
            ];
        });

        return view('admin.products.create', compact('categories'));
    }






    public function update(Request $request, Product $product)
    {
        $form = json_decode($request->input('form'), true);
    
        \Log::info('🔹 Update Product START', [
            'product_id' => $product->id,
            'request_all' => $request->all(),
            'form' => $form,
        ]);
    
        DB::beginTransaction();
    
        try {
            // Основні дані
            $this->updateProduct($product, $form);
            \Log::info('✅ updateProduct ok', ['product_id' => $product->id]);
    
            // Переклади
            $this->updateProductTranslations($product, $form);
            \Log::info('✅ updateProductTranslations ok');
    
            // Категорії
            $this->updateCategories($product, $form);
            \Log::info('✅ updateCategories ok');
    
            // Варіанти
            $this->updateVariants($product, $form['variants'] ?? []);
            \Log::info('✅ updateVariants ok');
    
            // Характеристики
            $this->updateAttributes($product, $form['attributes'] ?? []);
            \Log::info('✅ updateAttributes ok');
    
            // Кольори
            $this->updateColors($product, $form['colors'] ?? []);
            \Log::info('✅ updateColors ok');
    
            //Опис
            if (isset($form['description'])) {
                $this->updateProductDescription($product, $form['description']);
                \Log::info('✅ updateProductDescription ok');
            }
    
            // Зображення
            $this->updateProductImages($product, $request);
            \Log::info('✅ updateProductImages ok');
    
            DB::commit();
    
            \Log::info('🎉 Product update success', ['product_id' => $product->id]);
    
            return response()->json(['success' => true, 'message' => 'Продукт оновлено']);
        } catch (\Throwable $e) {
            DB::rollBack();
    
            \Log::error('❌ Update product FAILED', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'form' => $form,
            ]);
    
            return response()->json(['error' => 'Помилка оновлення: ' . $e->getMessage()], 500);
        }
    }
    
    

    public function updateProductImages(Product $product, Request $request)
    {
        // Отримуємо метадані зображень із запиту
        $imagesMetaRaw = $request->input('images_meta', '[]');
        $imagesMeta = json_decode($imagesMetaRaw, true) ?: [];
    
        // Файли, що завантажені з форми
        $uploadedFiles = $request->file('images', []);
    
        // Отримуємо вже існуючі зображення продукту
        $existingImages = $product->images()->get();
    
        // Масив URL-ів для порівняння, виключаючи тимчасові blob URL
        $urlsInRequest = [];
        foreach ($imagesMeta as $meta) {
            if (!empty($meta['url']) && !str_starts_with($meta['url'], 'blob:')) {
                $urlsInRequest[] = $meta['url'];
            }
        }
    
        // Видаляємо зображення, яких немає у запиті
        foreach ($existingImages as $image) {
            if (!in_array($image->url, $urlsInRequest)) {
                if (\Storage::disk('public')->exists($image->url)) {
                    \Storage::disk('public')->delete($image->url);
                }
                $image->delete();
            }
        }
    
        // Оновлюємо позиції і is_main для існуючих зображень
        foreach ($imagesMeta as $meta) {
            if (!empty($meta['url']) && !str_starts_with($meta['url'], 'blob:')) {
                $urlToFind = ltrim($meta['url'], '/');
                $image = $existingImages->first(function ($img) use ($urlToFind) {
                    return ltrim($img->url, '/') === $urlToFind;
                });
                if ($image) {
                    $image->position = $meta['position'] ?? 0;
                    $image->is_main = !empty($meta['is_main']);
                    $image->save();
                }
            }
        }
    
        // Папка для збереження файлів
        $folder = "products/{$product->id}";
        if (!\Storage::disk('public')->exists($folder)) {
            \Storage::disk('public')->makeDirectory($folder);
        }
    
        // Отримуємо назву продукту з перекладу українською для генерації імені файлу
        $name = optional($product->translations->firstWhere('locale', 'uk'))->name ?? 'product';
    
        // Додаємо нові файли
        foreach ($uploadedFiles as $index => $file) {
            $extension = $file->getClientOriginalExtension();
            $filename = $this->generateFilename($name, $extension);
            $file->storeAs($folder, $filename, 'public');
    
            $position = $imagesMeta[$index]['position'] ?? 0;
            $isMain = !empty($imagesMeta[$index]['is_main']);
    
            ProductImage::create([
                'product_id' => $product->id,
                'url' => "/storage/{$folder}/{$filename}",
                'position' => (int)$position,
                'is_main' => (bool)$isMain,
            ]);
        }
    }
    
    
    private function generateFilename(string $title, string $extension): string
    {
        $slug = \Str::slug($title, '-');
        $slug = mb_substr($slug, 0, 65);
        $rand = rand(100, 999);
        return "{$slug}-{$rand}.{$extension}";
    }
    
    
    
    
    
    

    
    
    protected function updateProductDescription(Product $product, array $description)
    {
        foreach (['uk', 'ru'] as $locale) {
            if (!isset($description[$locale])) {
                continue;
            }

            $translation = ProductTranslation::firstOrNew([
                'product_id' => $product->id,
                'locale' => $locale,
            ]);

            $translation->description = json_encode($description[$locale], JSON_UNESCAPED_UNICODE);
            $translation->save();
        }
    }


    protected function updateProduct(Product $product, array $data): void
    {
        $product->update([
            'sku' => $data['sku'] ?? $product->sku,
            'price' => $data['price'] ?? $product->price,
            'quantity_in_stock' => $data['quantity_in_stock'] ?? $product->quantity_in_stock,
            'status' => $data['status'] ?? $product->status,
            'is_popular' => $data['is_popular'] ?? $product->is_popular,
            'size_guide_id' => $data['size_guide_id'] ?? $product->size_guide_id,
        ]);
    }

    protected function updateProductTranslations(Product $product, array $data): void
    {
        $translations = [
            'uk' => [
                'name' => $data['name_uk'] ?? null,
                'slug' => $data['slug_uk'] ?? null,
                'meta_title' => $data['meta_title_uk'] ?? null,
                'meta_description' => $data['meta_description_uk'] ?? null,
                // 'description' можна додати, якщо є
            ],
            'ru' => [
                'name' => $data['name_ru'] ?? null,
                'slug' => $data['slug_ru'] ?? null,
                'meta_title' => $data['meta_title_ru'] ?? null,
                'meta_description' => $data['meta_description_ru'] ?? null,
            ],
        ];

        foreach ($translations as $locale => $fields) {
            $translation = $product->translations()->where('locale', $locale)->first();

            if ($translation) {
                $translation->update(array_filter($fields));
            } else {
                $product->translations()->create(array_merge($fields, ['locale' => $locale]));
            }
        }
    }

    protected function updateCategories(Product $product, array $data): void
    {
        if (isset($data['categories']) && is_array($data['categories'])) {
            $product->categories()->sync($data['categories']);
        }
    }

    protected function updateVariants(Product $product, array $variantsData)
    {
        $existingVariants = $product->variants()->get()->keyBy('id');
    
        $receivedIds = [];
    
        foreach ($variantsData as $variantData) {
            if (isset($variantData['id']) && $existingVariants->has($variantData['id'])) {
                $variant = $existingVariants->get($variantData['id']);
                $variant->update($variantData);
                $receivedIds[] = $variant->id;
            } else {
                $newVariant = $product->variants()->create($variantData);
                $receivedIds[] = $newVariant->id;
            }
        }
    
        $toDelete = $existingVariants->keys()->diff($receivedIds);
        if ($toDelete->isNotEmpty()) {
            $product->variants()->whereIn('id', $toDelete)->delete();
        }
    }
    protected function updateAttributes(Product $product, array $attributesData)
    {
        // Очікуємо $attributesData = ['uk' => [...], 'ru' => [...]]
        // Кожен елемент: ['name' => '...', 'value' => '...']

        $attributeMap = []; // slug атрибута => модель ProductAttribute
        $valueMap = []; // key: attribute_id + value_slug => ProductAttributeValue

        $attributeValuesToAttach = [];

        foreach (['uk', 'ru'] as $locale) {
            if (!isset($attributesData[$locale])) continue;

            foreach ($attributesData[$locale] as $item) {
                $attrName = $item['name'];
                $attrSlug = \Str::slug($attrName);
                $attrValue = $item['value'];
                $valueSlug = \Str::slug($attrValue);

                // --- Обробка атрибута ---
                if (!isset($attributeMap[$attrSlug])) {
                    $attribute = \App\Models\ProductAttribute::firstOrCreate(['slug' => $attrSlug], [
                        'type' => 'text', // можна додати логіку визначення типу
                        'is_filterable' => false,
                        'position' => 0,
                    ]);
                    $attributeMap[$attrSlug] = $attribute;
                } else {
                    $attribute = $attributeMap[$attrSlug];
                }

                // Оновлення перекладів атрибута
                $attrTranslation = $attribute->translations()->where('locale', $locale)->first();
                if ($attrTranslation) {
                    if ($attrTranslation->name !== $attrName) {
                        $attrTranslation->update(['name' => $attrName]);
                    }
                } else {
                    $attribute->translations()->create([
                        'locale' => $locale,
                        'name' => $attrName
                    ]);
                }

                // --- Обробка значення атрибута ---
                $valueKey = $attribute->id . '_' . $valueSlug;
                if (!isset($valueMap[$valueKey])) {
                    $attributeValue = \App\Models\ProductAttributeValue::firstOrCreate([
                        'product_attribute_id' => $attribute->id,
                    ]);
                    $valueMap[$valueKey] = $attributeValue;
                } else {
                    $attributeValue = $valueMap[$valueKey];
                }

                // Оновлення перекладів значення атрибута
                $valueTranslation = $attributeValue->translations()->where('locale', $locale)->first();
                if ($valueTranslation) {
                    if ($valueTranslation->value !== $attrValue || $valueTranslation->slug !== $valueSlug) {
                        $valueTranslation->update([
                            'value' => $attrValue,
                            'slug' => $valueSlug
                        ]);
                    }
                } else {
                    $attributeValue->translations()->create([
                        'locale' => $locale,
                        'value' => $attrValue,
                        'slug' => $valueSlug
                    ]);
                }

                $attributeValuesToAttach[] = $attributeValue->id;
            }
        }

        // Синхронізуємо звʼязок product_attribute_product
        $product->attributeValues()->sync(array_unique($attributeValuesToAttach));
    }


    protected function updateColors(Product $product, array $colorsData)
    {
        $existingColors = $product->colors()->get()->keyBy('id');
        $receivedIds = [];

        foreach ($colorsData as $colorData) {
            $colorData = collect($colorData);

            if ($colorData->has('id') && $existingColors->has($colorData['id'])) {
                $color = $existingColors->get($colorData['id']);
                // Оновлюємо всі поля, крім icon_path, якщо не передано новий файл
                $updateData = $colorData->except('icon_path', 'id')->toArray();
                $color->update($updateData);
                $receivedIds[] = $color->id;
            } else {
                // Створюємо новий колір
                $newColorData = $colorData->except('id')->toArray();
                $newColor = $product->colors()->create($newColorData);
                $receivedIds[] = $newColor->id;
            }

            // Якщо є логіка завантаження/оновлення icon_path (файл),
            // то її потрібно реалізувати окремо, наприклад через окремий метод
        }

        // Видаляємо кольори, яких немає у нових даних
        $toDelete = $existingColors->keys()->diff($receivedIds);
        if ($toDelete->isNotEmpty()) {
            $product->colors()->whereIn('id', $toDelete)->delete();
        }
    }

    











    /**
     * Збереження нового продукту (admin, AJAX)
     */
    public function store(Request $request)
    {
        $data = $request->input('form');
        $parsed = json_decode($data, true);
    
        // ✅ Валідація
        if ($validator = $this->validateProductData($request, $parsed)) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        DB::beginTransaction();
        try {
            // ✅ Створення продукту
            $product = Product::create([
                'sku'               => $parsed['sku'] ?? null,
                'price'             => $parsed['price'] ?? null,
                'quantity_in_stock' => $parsed['quantity_in_stock'] ?? null,
                'status'            => $parsed['status'] ?? 1,
                'size_guide_id'     => $parsed['size_guide_id'] ?? null,
                'is_popular'        => $parsed['is_popular'] ?? false,
            ]);
    
            // ✅ Варіанти
            if (!empty($parsed['variants'])) {
                $this->saveProductVariants($product->id, $parsed['variants']);
            }
    
            // ✅ Категорії
            $this->syncProductCategories($product, $parsed['categories'] ?? []);
    
            // ✅ Опис
            if (!empty($parsed['description'])) {
                $parsed['description'] = $this->handleDescriptionImages($parsed['description'], $product->id);
            }
    
            // ✅ Переклади (уніфіковано з update)
            foreach (['uk', 'ru'] as $locale) {
                $fields = [
                    'name'             => $parsed["name_{$locale}"] ?? null,
                    'slug'             => $parsed["slug_{$locale}"] ?? null,
                    'meta_title'       => $parsed["meta_title_{$locale}"] ?? null,
                    'meta_description' => $parsed["meta_description_{$locale}"] ?? null,
                    'description'      => isset($parsed['description'][$locale])
                                            ? json_encode($parsed['description'][$locale], JSON_UNESCAPED_UNICODE)
                                            : null,
                ];
    
                if (empty($fields['slug']) && !empty($fields['name'])) {
                    $fields['slug'] = $this->generateUniqueSlug($fields['name'], $product->id, $locale);
                }
    
                $product->translations()->updateOrCreate(
                    ['locale' => $locale],
                    array_filter($fields)
                );
            }
    
            // ✅ Кольори
            if (!empty($parsed['colors'])) {
                $this->handleColorImages($parsed['colors'], $product->id);
                $this->saveProductColors($product->id, $parsed['colors']);
            }
    
            // ✅ Галерея
            $imagePaths = [];
            if ($request->hasFile('images')) {
                $imagePaths = $this->saveProductImages(
                    $product->id,
                    $request->file('images'),
                    $request->input('images_meta', []),
                    $parsed['name_uk'] ?? 'dream-v-doma'
                );
            }
            $this->saveProductImagesToDB($product->id, $imagePaths);
    
            // ✅ Характеристики
            if (!empty($parsed['attributes'])) {
                $this->saveProductAttributes($product->id, $parsed['attributes']);
            }
    
            DB::commit();
    
            return response()->json([
                'success'     => true,
                'product_id'  => $product->id,
                'images'      => $imagePaths,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('❌ Store product FAILED', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'form'  => $parsed,
            ]);
            return response()->json([
                'error' => 'Сталася помилка при збереженні продукту: ' . $e->getMessage()
            ], 500);
        }
    }
    

    /**
     * Зберігає base64-зображення для description у /images/description/{productId}/
     * та повертає оновлений description з url картинок
     */
    private function handleDescriptionImages($description, $productId)
    {
        foreach (['uk', 'ru'] as $lang) {
            if (!empty($description[$lang]) && is_array($description[$lang])) {
                foreach ($description[$lang] as &$block) {
                    foreach (['imageUrl', 'imageUrl1', 'imageUrl2'] as $imgKey) {
                        if (!empty($block[$imgKey]) && str_starts_with($block[$imgKey], 'data:image')) {
                            // Якщо це base64, то зберігаємо файл і записуємо URL
                            $block[$imgKey] = $this->saveDescriptionBase64Image($block[$imgKey], $productId);
                        }
                        // Якщо там вже URL — нічого не робимо
                    }
                }
                unset($block);
            }
        }
        return $description;
    }

    /**
     * Зберігає одну base64-картинку для description і повертає URL до /storage/
     */
    private function saveDescriptionBase64Image($base64String, $productId)
    {
        try {
            if (!$base64String) return null;
            // Витягуємо розширення
            if (!preg_match('/^data:image\/(\w+);base64,/', $base64String, $matches)) return null;
            $ext = strtolower($matches[1]);
            $data = substr($base64String, strpos($base64String, ',') + 1);
            $data = base64_decode($data);
            if ($data === false) return null;

            $dir = "images/description/{$productId}/";
            $now = date('Ymd-His');
            $rand = rand(100, 999);
            $fileName = "zhinochi-domashni-kapci-rezynovi-tapki-{$now}-{$rand}.{$ext}";
            $path = $dir . $fileName;
            Storage::disk('public')->put($path, $data);

            return '/storage/' . $path;
        } catch (\Throwable $e) {
            \Log::error('[saveDescriptionBase64Image] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Зберігає кольори (ProductColor) для продукту,
     * а також генерує url для кожної мови до прив’язаного продукту цього кольору
     */
    private function saveProductColors($productId, $colors)
    {
        foreach ($colors as $color) {
            // ID продукту, на який веде цей колір
            $linkedProductId = $color['linked_product_id'] ?? null;

            // Генеруємо url для кожної мови, якщо є зв’язаний продукт
            $urls = [
                'uk' => null,
                'ru' => null,
            ];

            if ($linkedProductId) {
                $uk = \App\Models\ProductTranslation::where('product_id', $linkedProductId)->where('locale', 'uk')->first();
                $ru = \App\Models\ProductTranslation::where('product_id', $linkedProductId)->where('locale', 'ru')->first();

                $urls['uk'] = $uk?->slug ? url("/uk/product/" . $uk->slug) : null;
                $urls['ru'] = $ru?->slug ? url("/ru/product/" . $ru->slug) : null;
            }

            // Створюємо новий колір
            \App\Models\ProductColor::create([
                'product_id'        => $productId,                   // основний продукт
                'linked_product_id' => $linkedProductId,             // продукт, на який веде колір
                'name'              => $color['color'],
                'url'               => json_encode($urls, JSON_UNESCAPED_UNICODE),
                'icon_path'         => $color['image'],
                'is_default'        => isset($color['is_default']) ? (bool)$color['is_default'] : false,
            ]);
        }
    }


    /**
     * Зберігає base64-картинки для кольорів у /storage/colors/{productId}/
     */
    private function handleColorImages(array &$colors, $productId)
    {
        foreach ($colors as $i => $color) {
            if (!empty($color['image']) && strpos($color['image'], 'data:image') === 0) {
                [$typeInfo, $base64] = explode(',', $color['image']);
                $ext = 'png';
                if (preg_match('/data:image\/(\w+);base64/', $typeInfo, $matches)) {
                    $ext = $matches[1];
                }
                $image = base64_decode($base64);
                $folder = "colors/{$productId}/";
                $now = date('Ymd-His');
                $rand = rand(100, 999);
                $fileName = "zhinochi-domashni-kapci-rezynovi-tapki-color-{$now}-{$rand}.{$ext}";
                Storage::disk('public')->put($folder . $fileName, $image);
                $colors[$i]['image'] = "storage/{$folder}{$fileName}";
            }
        }
    }

    /**
     * Зберігає зображення продукту (upload в /storage/products/{productId}/)
     *
     * @return array $paths масив шляхів до зображень
     */
    private function saveProductImages($productId, $images, $meta = [], $title = 'dream-v-doma')
    {
        if (is_string($meta)) {
            $meta = json_decode($meta, true);
        }

        $savedImages = [];

        // Хелпер для формування імені файлу
        $generateFilename = function(string $title, string $extension): string {
            $slug = Str::slug($title, '-');
            $slug = mb_substr($slug, 0, 65);
            $rand = rand(100, 999);
            return "{$slug}-{$rand}.{$extension}";
        };

        $folder = "products/{$productId}";

        if (!\Storage::disk('public')->exists($folder)) {
            \Storage::disk('public')->makeDirectory($folder);
        }

        foreach ($images as $idx => $file) {
            $extension = $file->getClientOriginalExtension();
            $filename = $generateFilename($title, $extension);
            $file->storeAs($folder, $filename, 'public');

            // Метадані: is_main та position (якщо немає - дефолт)
            $position = isset($meta[$idx]['position']) ? (int)$meta[$idx]['position'] : $idx;
            $isMain   = isset($meta[$idx]['is_main']) ? (int)$meta[$idx]['is_main'] : 0;

            $savedImages[] = [
                'url'      => "/{$folder}/{$filename}",
                'position' => $position,
                'is_main'  => $isMain,
            ];
        }

        return $savedImages;
    }

    /**
     * Зберігає url зображень продукту у таблицю product_images
     */
    private function saveProductImagesToDB($productId, array $images)
    {
        foreach ($images as $img) {
            \App\Models\ProductImage::create([
                'product_id' => $productId,
                'url'        => $img['url'],
                'is_main'    => isset($img['is_main']) ? (int)$img['is_main'] : 0,
                'position'   => isset($img['position']) ? (int)$img['position'] : 0,
            ]);
        }
    }

    /**
     * Видає ajax-список продуктів (для автокомпліту/додавання до чогось)
     */
    public function list()
    {
        $locale = 'uk'; // для адмінки беремо тільки укр мову
        $products = Product::with(['translations' => function($q) use ($locale) {
            $q->where('locale', $locale);
        }])->select('id', 'sku')
          ->with('images')
          ->get()
          ->map(function ($product) {
              $name = $product->translations->first()->name ?? 'Без назви';
              $image = $product->images->first()->url ?? null;
              return [
                  'id' => $product->id,
                  'name' => $name,
                  'sku' => $product->sku,
                  'image' => $image,
              ];
          });

        return response()->json($products);
    }

    /**
     * Валідація даних продукту для збереження (admin форма)
     */
    private function validateProductData(Request $request, $parsed)
    {
        $validator = \Validator::make($parsed, [
            'name_uk'            => 'required|string',
            'name_ru'            => 'required|string',
            'sku'                => 'required|string|max:255',
            'price'              => 'required|numeric',
            'quantity_in_stock'  => 'required|integer',
            'categories'         => 'required|array|min:1',
            'size_guide_id'      => 'required|exists:size_guides,id',
        ], 
        [
            'name_uk.required'            => 'Поле "Назва (укр)" є обовʼязковим',
            'name_ru.required'            => 'Поле "Назва (рос)" є обовʼязковим',
            'sku.required'                => 'Поле "Артикул (SKU)" є обовʼязковим',
            'price.required'              => 'Поле "Ціна" є обовʼязковим',
            'price.numeric'               => 'Поле "Ціна" має бути числом',
            'quantity_in_stock.required'  => 'Поле "Кількість" є обовʼязковим',
            'quantity_in_stock.integer'   => 'Поле "Кількість" має бути цілим числом',
            'categories.required'         => 'Поле "Категорія" є обовʼязковим',
            'categories.array'            => 'Категорія повинна бути масивом',
            'categories.min'              => 'Обери хоча б одну категорію',
            'size_guide_id.required'      => 'Поле "Розмірна сітка" є обовʼязковим',
            'size_guide_id.exists'        => 'Обрана розмірна сітка не існує',
        ]);
    
        if ($validator->fails()) {
            return $validator;
        }
        return null;
    }

    /**
     * Генерує унікальний slug для продукту з перевіркою наявності (на мові $locale)
     */
    private function generateUniqueSlug(string $title, int $productId = null, string $locale = 'uk'): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (true) {
            $query = DB::table('product_translations')
                ->where('slug', $slug)
                ->where('locale', $locale);

            if ($productId) {
                $query->where('product_id', '!=', $productId);
            }

            if (!$query->exists()) {
                break;
            }

            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Синхронізує категорії продукту (many-to-many)
     * @param \App\Models\Product $product
     * @param array $categories - масив категорій [{id, name}, ...]
     */
    private function syncProductCategories($product, $categories)
    {
        $categoryIds = collect($categories)->pluck('id')->all();
        $product->categories()->sync($categoryIds);
    }

    /**
     * Зберігає варіанти продукту (розміри, кольори, і т.д.)
     */
    private function saveProductVariants($productId, array $variants)
    {
        foreach ($variants as $variant) {
            \App\Models\ProductVariant::create([
                'product_id'      => $productId,
                'name'            => $variant['name'] ?? null,
                'type'            => $variant['type'] ?? null,
                'size'            => $variant['size'] ?? null,
                'color'           => $variant['color'] ?? null,
                'quantity'        => $variant['quantity'] ?? 0,
                'price_override'  => $variant['price'] ?? null,
                'old_price'       => $variant['old_price'] ?? 0,
            ]);
        }
    }

    /**
     * Зберігає характеристики продукту (атрибут + значення + переклади + прив'язка до продукту)
     */
    public function saveProductAttributes($productId, array $attributes)
    {
        $locales = ['uk', 'ru'];
        $pairs = [];

        foreach ($locales as $locale) {
            foreach ($attributes[$locale] ?? [] as $attr) {
                $attrName = trim($attr['name']);
                $attrValue = trim($attr['value']);
                if (!$attrName || !$attrValue) continue;
                $attrSlug = \Str::slug($attrName);
                $valueSlug = \Str::slug($attrValue);

                // Атрибут (характеристика)
                if (!isset($pairs[$attrSlug])) {
                    $pairs[$attrSlug] = [
                        'translations' => [],
                        'values' => [],
                    ];
                }
                $pairs[$attrSlug]['translations'][$locale] = $attrName;

                // Значення
                if (!isset($pairs[$attrSlug]['values'][$valueSlug])) {
                    $pairs[$attrSlug]['values'][$valueSlug] = [
                        'translations' => [],
                    ];
                }
                $pairs[$attrSlug]['values'][$valueSlug]['translations'][$locale] = $attrValue;
            }
        }

        $attributeIds = [];
        $attributeValueIds = [];

        foreach ($pairs as $attrSlug => $attrData) {
            // 1. Атрибут (характеристика)
            $attribute = \App\Models\ProductAttribute::firstOrCreate(
                ['slug' => $attrSlug],
                ['type' => 'text', 'is_filterable' => true, 'position' => 0]
            );
            $attributeIds[$attrSlug] = $attribute->id;

            // Переклади атрибута
            foreach ($attrData['translations'] as $locale => $name) {
                \App\Models\ProductAttributeTranslation::updateOrCreate(
                    ['product_attribute_id' => $attribute->id, 'locale' => $locale],
                    ['name' => $name]
                );
            }

            // 2. Значення (value)
            foreach ($attrData['values'] as $valueSlug => $valData) {
                $attrValue = \App\Models\ProductAttributeValue::firstOrCreate([
                    'product_attribute_id' => $attribute->id
                ]);
                $attributeValueIds[$attrSlug][$valueSlug] = $attrValue->id;

                // Переклади значення
                foreach ($valData['translations'] as $locale => $value) {
                    \App\Models\ProductAttributeValueTranslation::updateOrCreate(
                        ['product_attribute_value_id' => $attrValue->id, 'locale' => $locale],
                        ['value' => $value, 'slug' => $valueSlug]
                    );
                }

                // 3. Прив'язка до продукту (pivot)
                \App\Models\ProductAttributeProduct::firstOrCreate([
                    'product_id' => $productId,
                    'product_attribute_value_id' => $attrValue->id,
                ]);
            }
        }
    }
}
