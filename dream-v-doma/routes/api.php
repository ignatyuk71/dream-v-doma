<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\InstagramPostController;
use App\Http\Controllers\Api\NovaPoshtaController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;

Route::middleware('api')->group(function () {
    // Продукти
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/categories', [ProductController::class, 'categories']);
    Route::get('/categories/{slug}', [Api\CategoryController::class, 'show']);

    // Instagram пости
    Route::get('/instagram-posts', [InstagramPostController::class, 'index']);

    // Нова Пошта
    Route::get('/nova-poshta/cities', [NovaPoshtaController::class, 'searchCities']);
    Route::get('/nova-poshta/warehouses', [NovaPoshtaController::class, 'getWarehouses']);
    Route::get('/nova-poshta/warehouse/{ref}', [NovaPoshtaController::class, 'getWarehouseByRef']);

    // Замовлення
    Route::prefix('orders')->group(function () {
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/{order}', [OrderController::class, 'show']);
    });
    
});
