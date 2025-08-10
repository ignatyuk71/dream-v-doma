<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstagramPost;
use Illuminate\Http\Request;

class InstagramPostController extends Controller
{
    public function index()
    {
        $posts = InstagramPost::orderBy('position')->get();
        return view('admin.instagram_posts.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.instagram_posts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp',
            'alt' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'active' => 'boolean',
            'position' => 'integer',
        ]);

        // Зберігаємо файл у storage/app/public/instagram
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('instagram_post', 'public');
            $data['image'] = 'storage/' . $path; // Для asset()
        }

        $data['active'] = $request->has('active') ? 1 : 0;

        InstagramPost::create($data);

        return redirect()->route('admin.instagram-posts.index')->with('success', 'Пост додано');
    }


    public function edit(InstagramPost $instagram_post)
    {
    
    }

    public function update(Request $request, InstagramPost $post)
    {
        
    }




    

    public function destroy(InstagramPost $instagram_post)
    {
        if ($instagram_post->image && str_starts_with($instagram_post->image, 'storage/instagram_post/')) {
            // Видалити файл з disk('public')
            $path = str_replace('storage/', '', $instagram_post->image);
            \Storage::disk('public')->delete($path);
        }
    
        $instagram_post->delete();
        return redirect()->route('admin.instagram-posts.index')->with('success', 'Пост видалено');
    }
    
    
}
