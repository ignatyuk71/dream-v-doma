<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'category_id' => 'nullable|integer',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $product = Product::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'price' => $data['price'],
            'category_id' => $data['category_id'] ?? null,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('products', 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                ]);
            }
        }

        return response()->json(['message' => 'Товар створено', 'product' => $product], 201);
    }

    public function categories()
    {
        return response()->json(\App\Models\Category::all());
    }
}