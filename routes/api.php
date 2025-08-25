<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\InstagramPostController;
use App\Http\Controllers\Api\NovaPoshtaController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;
use App\Http\Controllers\Api\UploadController;

Route::middleware('api')->group(function () {
    // Продукти
    Route::get('/products/list', [ProductController::class, 'list']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::post('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);

    Route::post('/products/get-linked-info', [ProductController::class, 'getLinkedInfo']);

    Route::post('/upload-image', [UploadController::class, 'uploadImage']);
    Route::post('/upload-image-color', [UploadController::class, 'uploadImageColor']);
    Route::post('/upload-image-category', [UploadController::class, 'uploadImageCategory']);


    // Категорії
    Route::get('/categories', [ProductController::class, 'categories']);
   
    Route::get('/category-select/{locale?}', [ApiCategoryController::class, 'select']);

    // === ДОДАНО: зміна статусу і parent категорії ===
    Route::post('/categories/{category}/toggle-status', [ApiCategoryController::class, 'toggleStatus']);
    Route::post('/categories/{category}/update-parent', [ApiCategoryController::class, 'updateParent']);
    // === /ДОДАНО ===
    Route::get('/categories/list-admin', [ApiCategoryController::class, 'listAdmin']);
    Route::post('/categories/update-order', [ApiCategoryController::class, 'updateOrder']);



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
