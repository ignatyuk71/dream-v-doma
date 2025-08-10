<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('translations')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $categories = Category::with('translations')->get();
        return view('admin.categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        \Log::info('REQUEST DATA:', $request->all());
        // Валідація даних
        $request->validate([
            'parent_id' => 'nullable|exists:categories,id',
    
            'name_uk' => 'required|string|max:255',
            'slug_uk' => 'nullable|string|max:255',
            'meta_title_uk' => 'nullable|string|max:255',
            'meta_description_uk' => 'nullable|string|max:1000',
    
            'name_ru' => 'required|string|max:255',
            'slug_ru' => 'nullable|string|max:255',
            'meta_title_ru' => 'nullable|string|max:255',
            'meta_description_ru' => 'nullable|string|max:1000',
        ]);
    
        // Створення категорії
        $category = new Category();
        $category->parent_id = $request->input('parent_id');
        $category->status = $request->input('status');
        $category->save();
    
        // ---- ГЕНЕРУЄМО SLUG ----
        $slugUk = $request->input('slug_uk');
        if (!$slugUk) {
            $slugUk = $this->ukr_to_latin($request->input('name_uk'));
        }
    
        $slugRu = $request->input('slug_ru');
        if (!$slugRu) {
            $slugRu = \Illuminate\Support\Str::slug($request->input('name_ru'), '-');
        }
    
        $descriptionUk = $request->input('description.uk');
        $descriptionRu = $request->input('description.ru');
        $descriptionUk = is_array($descriptionUk) ? $descriptionUk : [];
        $descriptionRu = is_array($descriptionRu) ? $descriptionRu : [];
    
        // Українська
        $ukTranslation = $category->translations()->firstOrNew(['locale' => 'uk']);
        $ukTranslation->name = $request->input('name_uk');
        $ukTranslation->slug = $slugUk;
        $ukTranslation->meta_title = $request->input('meta_title_uk');
        $ukTranslation->meta_description = $request->input('meta_description_uk');
        $ukTranslation->description = json_encode($descriptionUk, JSON_UNESCAPED_UNICODE);
        $ukTranslation->save();
    
        // Російська
        $ruTranslation = $category->translations()->firstOrNew(['locale' => 'ru']);
        $ruTranslation->name = $request->input('name_ru');
        $ruTranslation->slug = $slugRu;
        $ruTranslation->meta_title = $request->input('meta_title_ru');
        $ruTranslation->meta_description = $request->input('meta_description_ru');
        $ruTranslation->description = json_encode($descriptionRu, JSON_UNESCAPED_UNICODE);
        $ruTranslation->save();

        \Log::info('СОЗДАНА КАТЕГОРИЯ:', $category->toArray());
    \Log::info('TRANSLATION UK:', $ukTranslation->toArray());
    \Log::info('TRANSLATION RU:', $ruTranslation->toArray());

        return response()->json([
            'message' => 'Категорія успішно створена',
            'category' => $category->load('translations'),
        ]);
    }
    

    public function edit($id)
    {
        $category = Category::with('translations')->findOrFail($id);
    
        $descriptions = [
            'uk' => [],
            'ru' => [],
        ];
    
        foreach ($category->translations as $translation) {
            if (!empty($translation->description)) {
                $descriptions[$translation->locale] = json_decode($translation->description, true) ?: [];
            }
        }
    
        // Присвоюємо опис до окремої змінної (не напряму в модель)
        $categoryDescription = $descriptions;
    
        $categories = Category::with('translations')->get();
    
        return view('admin.categories.edit', compact('category', 'categories', 'categoryDescription'));
    }
    
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // Видаляємо директорію зображень (наприклад: storage/app/public/images/description_category/{id})
        $imageDir = storage_path("app/public/images/description_category/{$category->id}");
        if (is_dir($imageDir)) {
            \File::deleteDirectory($imageDir);
        }

        // Видаляємо всі переклади (якщо треба)
        $category->translations()->delete();

        // Видаляємо саму категорію
        $category->delete();

        return response()->json(['message' => 'Категорію та всі пов\'язані файли видалено!']);
    }

    


    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        // Валідація даних
        $request->validate([
            'parent_id' => 'nullable|exists:categories,id',
            'status' => 'required|boolean',

            'name_uk' => 'required|string|max:255',
            'slug_uk' => 'nullable|string|max:255',
            'meta_title_uk' => 'nullable|string|max:255',
            'meta_description_uk' => 'nullable|string|max:1000',

            'name_ru' => 'required|string|max:255',
            'slug_ru' => 'nullable|string|max:255',
            'meta_title_ru' => 'nullable|string|max:255',
            'meta_description_ru' => 'nullable|string|max:1000',
        ]);

        $category->parent_id = $request->input('parent_id');
        $category->status = $request->input('status');
        $category->save();

        // ---- ГЕНЕРУЄМО SLUG ----
        $slugUk = $request->input('slug_uk');
        if (!$slugUk) {
            $slugUk = $this->ukr_to_latin($request->input('name_uk'));
        }

        $slugRu = $request->input('slug_ru');
        if (!$slugRu) {
            $slugRu = \Illuminate\Support\Str::slug($request->input('name_ru'), '-');
        }

        $descriptionUk = $request->input('description.uk');
        $descriptionRu = $request->input('description.ru');
        $descriptionUk = is_array($descriptionUk) ? $descriptionUk : [];
        $descriptionRu = is_array($descriptionRu) ? $descriptionRu : [];

        $ukTranslation = $category->translations()->firstOrNew(['locale' => 'uk']);
        $ukTranslation->name = $request->input('name_uk');
        $ukTranslation->slug = $slugUk;
        $ukTranslation->meta_title = $request->input('meta_title_uk');
        $ukTranslation->meta_description = $request->input('meta_description_uk');
        $ukTranslation->description = json_encode($descriptionUk, JSON_UNESCAPED_UNICODE);
        $ukTranslation->save();

        $ruTranslation = $category->translations()->firstOrNew(['locale' => 'ru']);
        $ruTranslation->name = $request->input('name_ru');
        $ruTranslation->slug = $slugRu;
        $ruTranslation->meta_title = $request->input('meta_title_ru');
        $ruTranslation->meta_description = $request->input('meta_description_ru');
        $ruTranslation->description = json_encode($descriptionRu, JSON_UNESCAPED_UNICODE);
        $ruTranslation->save();

        return response()->json([
            'message' => 'Категорія успішно оновлена',
            'category' => $category->load('translations'),
        ]);
    }

    // ДОДАЙ ЦЮ ФУНКЦІЮ ВНИЗУ КЛАСУ
    private function ukr_to_latin($string)
    {
        $replace = [
            'А'=>'A','Б'=>'B','В'=>'V','Г'=>'H','Д'=>'D','Е'=>'E','Є'=>'Ye','Ж'=>'Zh','З'=>'Z',
            'И'=>'Y','І'=>'I','Ї'=>'Yi','Й'=>'Y','К'=>'K','Л'=>'L','М'=>'M','Н'=>'N','О'=>'O',
            'П'=>'P','Р'=>'R','С'=>'S','Т'=>'T','У'=>'U','Ф'=>'F','Х'=>'Kh','Ц'=>'Ts','Ч'=>'Ch',
            'Ш'=>'Sh','Щ'=>'Shch','Ь'=>'','Ю'=>'Yu','Я'=>'Ya',
            'а'=>'a','б'=>'b','в'=>'v','г'=>'h','д'=>'d','е'=>'e','є'=>'ye','ж'=>'zh','з'=>'z',
            'и'=>'y','і'=>'i','ї'=>'yi','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o',
            'п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'kh','ц'=>'ts','ч'=>'ch',
            'ш'=>'sh','щ'=>'shch','ь'=>'','ю'=>'yu','я'=>'ya',
            '’'=>'', '\''=>'',
        ];
        $str = strtr($string, $replace);
        $str = mb_strtolower($str, 'UTF-8');
        $str = preg_replace('~[^a-z0-9]+~u', '-', $str);
        $str = trim($str, '-');
        return $str;
    }
    

    
}
