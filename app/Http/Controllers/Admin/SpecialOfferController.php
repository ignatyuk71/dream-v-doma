<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SpecialOffer;

class SpecialOfferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $special_offers = SpecialOffer::orderBy('sort_order')->get();
        return view('admin.special_offers.index', compact('special_offers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.special_offers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image_path' => 'required|image',
            'preview_path' => 'nullable|image',
            'price' => 'required|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'discount' => 'nullable|integer|min:0|max:100',
            'button_text' => 'nullable|string|max:255',
            'button_link' => 'nullable|url|max:255',
            'expires_at' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
        $data['background_path'] = '#dceee7';
        if ($request->hasFile('image_path')) {
            $data['image_path'] = $request->file('image_path')->store('special_offers/images', 'public');
        }
        if ($request->hasFile('preview_path')) {
            $data['preview_path'] = $request->file('preview_path')->store('special_offers/previews', 'public');
        }
    
        // Дата завершення
        if (empty($data['expires_at'])) {
            $data['expires_at'] = null;
        } else {
            $data['expires_at'] = str_replace('T', ' ', $data['expires_at']) . ':00';
        }
    
        $data['is_active'] = $request->has('is_active') ? 1 : 0;
    
        \App\Models\SpecialOffer::create($data);
    
        return redirect()
            ->route('admin.special_offers.index')
            ->with('success', 'Спецпропозицію додано успішно!');
    }
    
    
    
    

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $specialOffer = SpecialOffer::findOrFail($id);
        return view('admin.special_offers.edit', compact('specialOffer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SpecialOffer $specialOffer)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image_path' => 'nullable|image',
            'preview_path' => 'nullable|image',
            'price' => 'required|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'discount' => 'nullable|integer|min:0|max:100',
            'button_text' => 'nullable|string|max:255',
            'button_link' => 'nullable|url|max:255',
            'expires_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);
    
        // Основне зображення
        if ($request->hasFile('image_path')) {
            // Видаляємо старе
            if ($specialOffer->image_path && \Storage::disk('public')->exists($specialOffer->image_path)) {
                \Storage::disk('public')->delete($specialOffer->image_path);
            }
            $data['image_path'] = $request->file('image_path')->store('special_offers/images', 'public');
        }
    
        // Зображення preview
        if ($request->hasFile('preview_path')) {
            if ($specialOffer->preview_path && \Storage::disk('public')->exists($specialOffer->preview_path)) {
                \Storage::disk('public')->delete($specialOffer->preview_path);
            }
            $data['preview_path'] = $request->file('preview_path')->store('special_offers/previews', 'public');
        }
    
        $data['is_active'] = $request->has('is_active') ? 1 : 0;
    
        // background_path завжди лишаємо '#dceee7'
        $data['background_path'] = '#dceee7';
    
        $specialOffer->update($data);
    
        return redirect()->route('admin.special_offers.index')
            ->with('success', 'Спецпропозицію оновлено успішно!');
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SpecialOffer $specialOffer)
    {
        // Видаляємо файли, якщо існують
        if ($specialOffer->image_path && \Storage::disk('public')->exists($specialOffer->image_path)) {
            \Storage::disk('public')->delete($specialOffer->image_path);
        }
    
        if ($specialOffer->preview_path && \Storage::disk('public')->exists($specialOffer->preview_path)) {
            \Storage::disk('public')->delete($specialOffer->preview_path);
        }
    
        // Видаляємо запис з бази
        $specialOffer->delete();
    
        return redirect()
            ->route('admin.special_offers.index')
            ->with('success', 'Спецпропозицію видалено успішно!');
    }
    
}
