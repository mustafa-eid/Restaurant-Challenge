<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderItemController;

/**
 * --------------------------------------------------------------------------
 * API Routes
 * --------------------------------------------------------------------------
 *
 * This file defines all API endpoints for the application.
 * Public routes are available without authentication, while
 * protected routes are secured using Laravel Sanctum middleware.
 *
 * Each route is grouped by resource type (Users, Products, Orders, OrderItems)
 * to maintain a RESTful and organized structure.
 *
 * @package Routes
 */

/**
 * --------------------------------------------------------------------------
 * Public Routes (No Authentication Required)
 * --------------------------------------------------------------------------
 */

/**
 * Register a new user.
 *
 * @route POST /api/register
 * @uses UserController::register
 */
Route::post('/register',                                        [UserController::class, 'register']);

/**
 * Login an existing user and issue an access token.
 *
 * @route POST /api/login
 * @uses UserController::login
 */
Route::post('/login',                                           [UserController::class, 'login']);

/**
 * --------------------------------------------------------------------------
 * Protected Routes (Require Authentication via Sanctum)
 * --------------------------------------------------------------------------
 */
Route::middleware('auth:sanctum')->group(function () {

    /**
     * ----------------------------------------------------------------------
     * User Routes
     * ----------------------------------------------------------------------
     */

    /**
     * Logout the currently authenticated user and revoke their token.
     *
     * @route POST /api/logout
     * @uses UserController::logout
     */
    Route::post('/logout',                                      [UserController::class, 'logout']);

    /**
     * ----------------------------------------------------------------------
     * Product Routes
     * ----------------------------------------------------------------------
     *
     * Handles CRUD operations for products.
     * All endpoints are prefixed with `/api/products`
     * and use named routes under the `products.` namespace.
     */
    Route::prefix('products')->name('products.')->group(function () {

        /**
         * Retrieve a paginated list of products.
         *
         * @route GET /api/products
         * @uses ProductController::index
         */
        Route::get('/',                                         [ProductController::class, 'index'])->name('index');

        /**
         * Create a new product.
         *
         * @route POST /api/products/create
         * @uses ProductController::store
         */
        Route::post('/create',                                  [ProductController::class, 'store'])->name('store');

        /**
         * Retrieve a specific product by ID.
         *
         * @route GET /api/products/show/{id}
         * @uses ProductController::show
         */
        Route::get('/show/{id}',                                [ProductController::class, 'show'])->name('show');

        /**
         * Update an existing product.
         *
         * @route PUT /api/products/update/{product}
         * @uses ProductController::update
         */
        Route::put('/update/{product}',                         [ProductController::class, 'update'])->name('update');

        /**
         * Delete a specific product.
         *
         * @route DELETE /api/products/delete/{product}
         * @uses ProductController::destroy
         */
        Route::delete('/delete/{product}',                      [ProductController::class, 'destroy'])->name('destroy');
    });

    /**
     * ----------------------------------------------------------------------
     * Order Routes
     * ----------------------------------------------------------------------
     *
     * Handles CRUD operations for customer orders.
     * All endpoints are prefixed with `/api/orders`
     * and use named routes under the `orders.` namespace.
     */
    Route::prefix('orders')->name('orders.')->group(function () {

        /**
         * Retrieve a paginated list of orders.
         *
         * @route GET /api/orders
         * @uses OrderController::index
         */
        Route::get('/',                                         [OrderController::class, 'index'])->name('index');

        /**
         * Create a new order.
         *
         * @route POST /api/orders/create
         * @uses OrderController::store
         */
        Route::post('/create',                                  [OrderController::class, 'store'])->name('store');

        /**
         * Retrieve a specific order by ID.
         *
         * @route GET /api/orders/show/{id}
         * @uses OrderController::show
         */
        Route::get('/show/{id}',                                [OrderController::class, 'show'])->name('show');

        /**
         * Update an existing order.
         *
         * @route PUT /api/orders/update/{order}
         * @uses OrderController::update
         */
        Route::put('/update/{order}',                           [OrderController::class, 'update'])->name('update');

        /**
         * Delete a specific order.
         *
         * @route DELETE /api/orders/delete/{order}
         * @uses OrderController::destroy
         */
        Route::delete('/delete/{order}',                        [OrderController::class, 'destroy'])->name('destroy');
    });

    /**
     * ----------------------------------------------------------------------
     * Order Item Routes
     * ----------------------------------------------------------------------
     *
     * Handles CRUD operations for items within orders.
     * All endpoints are prefixed with `/api/order_items`
     * and use named routes under the `order_items.` namespace.
     */
    Route::prefix('order_items')->name('order_items.')->group(function () {

        /**
         * Retrieve a paginated list of order items.
         *
         * @route GET /api/order_items
         * @uses OrderItemController::index
         */
        Route::get('/',                                         [OrderItemController::class, 'index'])->name('index');

        /**
         * Create a new order item.
         *
         * @route POST /api/order_items/create
         * @uses OrderItemController::store
         */
        Route::post('/create',                                  [OrderItemController::class, 'store'])->name('store');

        /**
         * Retrieve a specific order item by ID.
         *
         * @route GET /api/order_items/show/{id}
         * @uses OrderItemController::show
         */
        Route::get('/show/{id}',                                [OrderItemController::class, 'show'])->name('show');

        /**
         * Update an existing order item.
         *
         * @route PUT /api/order_items/update/{orderItem}
         * @uses OrderItemController::update
         */
        Route::put('/update/{orderItem}',                       [OrderItemController::class, 'update'])->name('update');

        /**
         * Delete a specific order item.
         *
         * @route DELETE /api/order_items/delete/{orderItem}
         * @uses OrderItemController::destroy
         */
        Route::delete('/delete/{orderItem}',                    [OrderItemController::class, 'destroy'])->name('destroy');
    });
});
