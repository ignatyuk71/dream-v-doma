<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InstagramPost;

class InstagramPostController extends Controller
{
    public function index()
    {
        return InstagramPost::latest()->take(10)->get();
    }
}
