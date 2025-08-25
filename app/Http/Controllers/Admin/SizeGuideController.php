<?php

// app/Http/Controllers/Admin/SizeGuideController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SizeGuide;

class SizeGuideController extends Controller
{
    public function list()
    {
        // Витягуємо всі сітки, тільки потрібні поля
        $guides = SizeGuide::all()->map(function ($guide) {
            return [
                'id'   => $guide->id,
                'name' => $guide->name_uk, // якщо треба — можеш зробити мультимовність
                'sizes' => $guide->data,   // тут вже array (через casts)
            ];
        });
        return response()->json($guides);
    }
}
