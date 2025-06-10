<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;

Route::middleware('api')->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/categories', [ProductController::class, 'categories']);
    Route::get('/products', [ProductController::class, 'index']); // додай це
});

