<?php

namespace App\Http\Controllers;

use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductReviewController extends Controller
{
    public function store(Request $request)
{
    $validated = $request->validate([
        'product_id'   => ['required', 'exists:products,id'],
        'author_name'  => ['required', 'string', 'max:255'],
        'rating'       => ['required', 'integer', 'between:1,5'],
        'content'      => ['required', 'string'],
        'photo'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp'], // до 2MB
    ]);

    // Збереження фото
    if ($request->hasFile('photo')) {
        $file = $request->file('photo');
        $ext = $file->getClientOriginalExtension();

        $date = now()->format('Ymd');
        $filename = "domashni-tapochki-dream-v-doma_{$date}." . $ext;

        // Зберегти файл
        $file->move(public_path('assets/img/review'), $filename);

        $validated['photo_path'] = "assets/img/review/" . $filename;
    } else {
        $validated['photo_path'] = null;
    }

    // Зберегти відгук
    ProductReview::create([
        'product_id'   => $validated['product_id'],
        'author_name'  => $validated['author_name'],
        'rating'       => $validated['rating'],
        'content'      => $validated['content'],
        'photo_path'   => $validated['photo_path'],
        'is_approved'  => false, // або true, якщо без модерації
    ]);

    return back()->with('success', 'Ваш відгук успішно надіслано та очікує модерації.');
}

}
