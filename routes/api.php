<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderItemController;

// Public routes
Route::post('/register',                                    [UserController::class, 'register']);
Route::post('/login',                                       [UserController::class, 'login']);

// Protected routes with Sanctum token
Route::middleware('auth:sanctum')->group(function () {

    // Logout
    Route::post('/logout',                                  [UserController::class, 'logout']);

    // Products routes
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/',                                     [ProductController::class, 'index'])->name('index');
        Route::post('/create',                              [ProductController::class, 'store'])->name('store');
        Route::get('/show/{id}',                            [ProductController::class, 'show'])->name('show');
        Route::put('/update/{product}',                     [ProductController::class, 'update'])->name('update');
        Route::delete('/delete/{product}',                  [ProductController::class, 'destroy'])->name('destroy');
    });

    // Orders routes
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/',                                     [OrderController::class, 'index'])->name('index');
        Route::post('/create',                              [OrderController::class, 'store'])->name('store');
        Route::get('/show/{id}',                            [OrderController::class, 'show'])->name('show');
        Route::put('/update/{order}',                       [OrderController::class, 'update'])->name('update');
        Route::delete('/delete/{order}',                    [OrderController::class, 'destroy'])->name('destroy');
    });

    // Order items routes
    Route::prefix('order_items')->name('order_items.')->group(function () {
        Route::get('/',                                     [OrderItemController::class, 'index'])->name('index');
        Route::post('/create',                              [OrderItemController::class, 'store'])->name('store');
        Route::get('/show/{id}',                            [OrderItemController::class, 'show'])->name('show');
        Route::put('/update/{orderItem}',                   [OrderItemController::class, 'update'])->name('update');
        Route::delete('/delete/{orderItem}',                [OrderItemController::class, 'destroy'])->name('destroy');
    });
});
