<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;

class PageController extends Controller
{
    public function about()
    {
        $locale = App::getLocale();

        // Наприклад, у майбутньому тут можна тягнути контент із бази
        return view('about', compact('locale'));
    }
}

