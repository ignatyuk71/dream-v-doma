<?php

namespace App\Http\Controllers\Admin;

use App\Models\Banner;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('sort_order')->get();
        return view('admin.banners.index', compact('banners'));
    }
    
    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'image' => 'required|image',
            'title' => 'nullable|string',
            'subtitle' => 'nullable|string',
            'button_text' => 'nullable|string',
            'button_link' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // Upload image з власною унікальною назвою у images/banner/
        if ($request->hasFile('image')) {
            $dir = 'images/banner/';
            $ext = $request->file('image')->getClientOriginalExtension();
            $now = date('Ymd-His');
            $rand = rand(100, 999);
            $fileName = "zhinochi-domashni-kapci-rezynovi-tapki-{$now}-{$rand}.{$ext}";
            $path = $dir . $fileName;
            Storage::disk('public')->put($path, file_get_contents($request->file('image')->getRealPath()));
            $data['image'] = $path;
        }
        $data['is_active'] = $request->has('is_active');

        Banner::create($data);

        return redirect()->route('admin.banners.index');
    }

    public function destroy(Banner $banner)
    {
        if ($banner->image && Storage::disk('public')->exists($banner->image)) {
            Storage::disk('public')->delete($banner->image);
        }
        $banner->delete();
        return back();
    }


    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $request->validate([
            'image' => 'nullable|image',
            'title' => 'nullable|string',
            'subtitle' => 'nullable|string',
            'button_text' => 'nullable|string',
            'button_link' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // Якщо прийшло нове зображення — зберігаємо й видаляємо старе
        if ($request->hasFile('image')) {
            // Видаляємо старе, якщо було
            if ($banner->image && \Storage::disk('public')->exists($banner->image)) {
                \Storage::disk('public')->delete($banner->image);
            }
            $dir = 'images/banner/';
            $ext = $request->file('image')->getClientOriginalExtension();
            $now = date('Ymd-His');
            $rand = rand(100, 999);
            $fileName = "zhinochi-domashni-kapci-rezynovi-tapki-{$now}-{$rand}.{$ext}";
            $path = $dir . $fileName;
            \Storage::disk('public')->put($path, file_get_contents($request->file('image')->getRealPath()));
            $data['image'] = $path;
        }

        $data['is_active'] = $request->has('is_active');

        $banner->update($data);

        return redirect()->route('admin.banners.index')->with('success', 'Банер оновлено');
    }

}
