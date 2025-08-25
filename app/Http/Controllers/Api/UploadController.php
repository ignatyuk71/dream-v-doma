<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{


    public function uploadImageColor(Request $request)
    {
        $productId = $request->input('product_id');
        $title = 'color-zhinochi-domashni-kapci-rezynovi-tapki-color';
    
        if (!$productId) {
            return response()->json(['error' => 'Не знайдено ID продукту! Збережіть товар перед додаванням фото.'], 400);
        }
    
        if ($request->hasFile('image')) {
            $folder = "colors/{$productId}";
    
            // Створюємо папку якщо треба
            if (!\Storage::disk('public')->exists($folder)) {
                \Storage::disk('public')->makeDirectory($folder);
            }
    
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $slug = \Str::slug($title, '-');
            $slug = mb_substr($slug, 0, 65);
            $rand = rand(100, 999);
            $filename = "{$slug}-{$rand}.{$extension}";
    
            // Зберігаємо файл
            $path = $file->storeAs($folder, $filename, 'public');
            $url = \Storage::url($path);
    
            return response()->json(['url' => $url]);
        }
    
        return response()->json(['error' => 'No file uploaded or no product_id'], 400);
    }
    

    public function uploadImage(Request $request)
    {
        $productId = $request->input('product_id');
        $title = 'dream-v-doma-zhinochi-domashni-kapci-rezynovi-tapki-color';

        if (!$productId) {
            return response()->json(['error' => 'Не знайдено ID продукту! Збережіть товар перед додаванням фото.'], 400);
        }

        if ($request->hasFile('image')) {
            $folder = "images/description/{$productId}";

            // Створюємо папку якщо треба
            if (!Storage::disk('public')->exists($folder)) {
                Storage::disk('public')->makeDirectory($folder);
            }

            // Генеруємо ім'я
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $slug = Str::slug($title, '-');
            $slug = mb_substr($slug, 0, 65);
            $rand = rand(100, 999);
            $filename = "{$slug}-{$rand}.{$extension}";

            // Зберігаємо файл
            $path = $file->storeAs($folder, $filename, 'public');
            $url = Storage::url($path);

            return response()->json(['url' => $url]);
        }

        return response()->json(['error' => 'No file uploaded or no product_id'], 400);
    }
    public function uploadImageCategory(Request $request)
    {
        $categoryId = $request->input('category_id');
        $title = 'dream-v-doma-zhinochi-domashni-kapci-rezynovi-tapki-category';

        if (!$categoryId) {
            return response()->json(['error' => 'Не знайдено ID категорії! Збережіть категорію перед додаванням фото.'], 400);
        }

        if ($request->hasFile('image')) {
            $folder = "images/description_category/{$categoryId}";

            if (!Storage::disk('public')->exists($folder)) {
                Storage::disk('public')->makeDirectory($folder);
            }

            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $slug = Str::slug($title, '-');
            $slug = mb_substr($slug, 0, 65);
            $rand = rand(100, 999);
            $filename = "{$slug}-{$rand}.{$extension}";

            $path = $file->storeAs($folder, $filename, 'public');
            $url = Storage::url($path);

            return response()->json(['url' => $url]);
        }

        return response()->json(['error' => 'No file uploaded or no category_id'], 400);
    }
    

}
