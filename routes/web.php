<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Api\InstagramPostController;
use App\Http\Controllers\Api\NovaPoshtaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;
use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;


// 🔁 Редірект з кореня на дефолтну мову
Route::redirect('/', '/uk/');

// 🔌 API без локалі
Route::get('/api/products', [\App\Http\Controllers\ProductController::class, 'home']);
Route::get('/api/instagram-posts', [InstagramPostController::class, 'index']);
Route::get('/nova-poshta/cities', [NovaPoshtaController::class, 'searchCities']);
Route::get('/nova-poshta/warehouses', [NovaPoshtaController::class, 'getWarehouses']);

// 🌐 Локалізовані маршрути (uk|ru)
Route::group(['prefix' => '{locale}', 'where' => ['locale' => 'uk|ru']], function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/about', [PageController::class, 'about'])->name('about');

    // 📡 API всередині локалі
    Route::get('/api/products', [\App\Http\Controllers\ProductController::class, 'home']);
    Route::get('/api/categories', [ApiCategoryController::class, 'index']);
    Route::get('/api/products/{slug}', [ApiProductController::class, 'show']);

    // 📝 Відгуки про товар
    Route::post('/product-reviews', [ProductReviewController::class, 'store'])->name('product-reviews.store');

    // 🧾 Замовлення
    Route::get('/checkout', fn () => view('checkout'))->name('checkout');
    Route::get('/thank-you', fn () => view('thank-you'))->name('thank-you');


    // 1) Категорія з фільтрами (другий сегмент починається з rozmir-|kolir-|tsina-)
    Route::get('/{category}/{filters}', [\App\Http\Controllers\CategoryController::class, 'show'])
        ->where([
            'category' => '[A-Za-z0-9\-_]+',
            'filters'  => '(?:rozmir|kolir|tsina).+', // ← ключове обмеження
        ])
        ->name('category.filtered');

    // 2) Товар (звичайний двосегментний URL категорія/продукт)
    Route::get('/{category}/{product}', [\App\Http\Controllers\ProductController::class, 'show'])
        ->where([
            'category' => '[A-Za-z0-9\-_]+',
            'product'  => '[A-Za-z0-9\-_]+',
        ])
        ->name('products.show');

    // 3) Чиста категорія
    Route::get('/{category}', [\App\Http\Controllers\CategoryController::class, 'show'])
        ->where([
            'category' => '[A-Za-z0-9\-_]+',
        ])
        ->name('category.show');

});

// 👤 Кабінет користувача
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 🔐 Адмінка
Route::middleware(['auth'])->group(function () {
    // Редірект /dashboard на /admin
    Route::get('/dashboard', fn () => redirect('/admin'))->name('dashboard');

    // Головна адмінка
    Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Товари
    Route::get('/admin/products', [ProductController::class, 'index'])->name('admin.products.index');
    Route::get('/admin/products/create', [ProductController::class, 'create'])->name('admin.products.create');
    Route::post('/admin/products', [ProductController::class, 'store'])->name('admin.products.store');
    Route::get('/admin/products/{product}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
    Route::put('/admin/products/{product}', [ProductController::class, 'update'])->name('admin.products.update');

    // Категорії
    Route::get('/admin/categories', [AdminCategoryController::class, 'index'])->name('admin.categories.index');
    Route::get('/admin/categories/create', [AdminCategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('/admin/categories', [AdminCategoryController::class, 'store'])->name('admin.categories.store');
    Route::get('/admin/categories/{category}/edit', [AdminCategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::put('/admin/categories/{category}', [AdminCategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/admin/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('admin.categories.destroy');

    // Банери
    Route::get('/admin/banners', [\App\Http\Controllers\Admin\BannerController::class, 'index'])->name('admin.banners.index');
    Route::get('/admin/banners/create', [\App\Http\Controllers\Admin\BannerController::class, 'create'])->name('admin.banners.create');
    Route::post('/admin/banners', [\App\Http\Controllers\Admin\BannerController::class, 'store'])->name('admin.banners.store');
    Route::delete('/admin/banners/{banner}', [\App\Http\Controllers\Admin\BannerController::class, 'destroy'])->name('admin.banners.destroy');
    Route::get('/admin/banners/{banner}/edit', [\App\Http\Controllers\Admin\BannerController::class, 'edit'])->name('admin.banners.edit');
    Route::put('/admin/banners/{banner}', [\App\Http\Controllers\Admin\BannerController::class, 'update'])->name('admin.banners.update');

    Route::resource('admin/instagram-posts', \App\Http\Controllers\Admin\InstagramPostController::class)
        ->names([
            'index' => 'admin.instagram-posts.index',
            'create' => 'admin.instagram-posts.create',
            'store' => 'admin.instagram-posts.store',
            'destroy' => 'admin.instagram-posts.destroy',
            'show' => 'admin.instagram-posts.show',
        ]);

    Route::resource('/admin/special_offers', \App\Http\Controllers\Admin\SpecialOfferController::class, [
        'as' => 'admin'
    ]);

    // Size guides
    Route::get('/admin/size-guides', [\App\Http\Controllers\Admin\SizeGuideController::class, 'list']);

    // Додатковий маршрут для отримання списку продуктів (API чи AJAX)
    Route::get('/admin/products/list', [App\Http\Controllers\Admin\ProductController::class, 'list']);

    // Замовлення (адмінка)
    Route::get('/admin/orders', [AdminOrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/admin/orders/{order}', [AdminOrderController::class, 'show'])->name('admin.orders.show');
    Route::patch('/admin/orders/{order}', [AdminOrderController::class, 'update'])->name('admin.orders.update');

    // ✅ ОКРЕМИЙ PATCH для оновлення СТАТУСУ (AJAX, без перезавантаження)
    Route::patch('/admin/orders/{order}/status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('admin.orders.status.update');
    // ✅ Видалення замовлення
    Route::delete('/admin/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'destroy'])->name('admin.orders.destroy');


    // =========================
    // Налаштування (Settings)
    // =========================

    // Огляд Pixel & CAPI
    Route::get('/admin/settings', [\App\Http\Controllers\Admin\TrackingSettingsController::class, 'index'])
        ->name('admin.settings_pixel.index');

    // Форма редагування Pixel & CAPI
    Route::get('/admin/settings/tracking', [\App\Http\Controllers\Admin\TrackingSettingsController::class, 'edit'])
        ->name('admin.settings_pixel.tracking');

    // Сабміт форми
    Route::match(['put','patch'], '/admin/settings/tracking', [\App\Http\Controllers\Admin\TrackingSettingsController::class, 'update'])
        ->name('admin.settings_pixel.tracking.update');

});

// 🔑 Аутентифікація
require __DIR__.'/auth.php';