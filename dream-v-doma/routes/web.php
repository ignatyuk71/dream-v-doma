<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Api\InstagramPostController;
use App\Http\Controllers\Api\NovaPoshtaController;

Route::get('/', function () {
    return view('home');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/api/products', [\App\Http\Controllers\ProductController::class, 'home']);
Route::get('/api/instagram-posts', [InstagramPostController::class, 'index']);

Route::get('/nova-poshta/cities', [NovaPoshtaController::class, 'searchCities']);
Route::get('/nova-poshta/warehouses', [NovaPoshtaController::class, 'getWarehouses']);


Route::group(['prefix' => '{locale}', 'where' => ['locale' => 'ua|ru']], function () {
    Route::get('/', fn () => view('home'));
    Route::get('/about', [\App\Http\Controllers\PageController::class, 'about'])->name('about');

    // API — вже всередині локалі
    Route::get('/api/products', [\App\Http\Controllers\ProductController::class, 'home']);
    Route::get('/api/categories', [\App\Http\Controllers\Api\CategoryController::class, 'index']);
    Route::get('/api/products/{slug}', [\App\Http\Controllers\Api\ProductController::class, 'show']);
    

    // Сторінка товару
    Route::get('/product/{slug}', [\App\Http\Controllers\ProductController::class, 'show']);

    Route::get('/checkout', function () {
        return view('checkout');
    })->name('checkout');
    Route::get('/thank-you', function () {
        return view('thank-you');
    })->name('thank-you');
   
    Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');

});



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/admin', fn () => view('admin.dashboard'))->name('admin.dashboard');
    Route::get('/admin/products', [ProductController::class, 'index'])->name('admin.products.index');
    
    Route::get('/admin/categories', [CategoryController::class, 'index'])->name('admin.categories.index');
    
    Route::get('/admin/categories/create', [CategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('/admin/categories', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::get('/admin/categories/{category}/edit', [CategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::put('/admin/categories/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/admin/categories/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');

    Route::get('/admin/products/create', [ProductController::class, 'create']);
    Route::post('/admin/products', [ProductController::class, 'store']);
    Route::get('/admin/products/{product}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
    Route::put('/admin/products/{product}', [ProductController::class, 'update'])->name('admin.products.update');
    Route::delete('/admin/products/{product}', [ProductController::class, 'destroy'])->name('admin.products.destroy');
    
});



require __DIR__.'/auth.php';
