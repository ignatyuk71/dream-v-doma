<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\InstagramPostController;
use App\Http\Controllers\Api\NovaPoshtaController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\TrackController;

Route::middleware('api')->group(function () {

    // ===== Продукти =====
    Route::get('/products/list', [ProductController::class, 'list']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::post('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    Route::post('/products/get-linked-info', [ProductController::class, 'getLinkedInfo']);

    // Завантаження зображень
    Route::post('/upload-image', [UploadController::class, 'uploadImage']);
    Route::post('/upload-image-color', [UploadController::class, 'uploadImageColor']);
    Route::post('/upload-image-category', [UploadController::class, 'uploadImageCategory']);

    // ===== Категорії =====
    Route::get('/categories', [ProductController::class, 'categories']);
    Route::get('/category-select/{locale?}', [ApiCategoryController::class, 'select']);
    Route::post('/categories/{category}/toggle-status', [ApiCategoryController::class, 'toggleStatus']);
    Route::post('/categories/{category}/update-parent', [ApiCategoryController::class, 'updateParent']);
    Route::get('/categories/list-admin', [ApiCategoryController::class, 'listAdmin']);
    Route::post('/categories/update-order', [ApiCategoryController::class, 'updateOrder']);

    // ===== Instagram =====
    Route::get('/instagram-posts', [InstagramPostController::class, 'index']);

    // ===== Нова Пошта =====
    Route::get('/nova-poshta/cities', [NovaPoshtaController::class, 'searchCities']);
    Route::get('/nova-poshta/warehouses', [NovaPoshtaController::class, 'getWarehouses']);
    Route::get('/nova-poshta/warehouse/{ref}', [NovaPoshtaController::class, 'getWarehouseByRef']);

    // ===== Замовлення =====
    Route::prefix('orders')->group(function () {
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/{order}', [OrderController::class, 'show']);
    });

    // ===== Tracking (CAPI + бек-сайд події) =====
    Route::prefix('track')
        // захист від флуду: не більше 120 запитів/хв з IP (підкрутиш за потреби)
        ->middleware('throttle:120,1')
        ->group(function () {
            // PageView — опційно (зробимо пізніше або вимкнемо)
            Route::post('/pv',   [TrackController::class, 'pv'])->name('track.pv');

            // ViewContent (перегляд товару)
            Route::post('/vc',   [TrackController::class, 'vc'])->name('track.vc');

            // AddToCart (додавання до кошика)
            Route::post('/atc',  [TrackController::class, 'atc'])->name('track.atc');

            // InitiateCheckout (перехід до оформлення)
            Route::post('/ic',   [TrackController::class, 'ic'])->name('track.ic');

            // Lead (заявка/лист/зворотній зв’язок)
            Route::post('/lead', [TrackController::class, 'lead'])->name('track.lead');

            Route::post('/purchase', [TrackController::class, 'purchase'])->name('track.purchase');

        });
    });
