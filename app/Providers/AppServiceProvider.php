<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Interfaces
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use App\Services\Contracts\PaymentGatewayInterface;
use App\Services\Contracts\InventoryManagerInterface;

// Implementations
use App\Repositories\UserRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\OrderItemRepository;
use App\Services\Payment\CheckoutPaymentGateway;
use App\Services\Inventory\InventoryManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repositories
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(OrderItemRepositoryInterface::class, OrderItemRepository::class);

        // Services
        $this->app->bind(PaymentGatewayInterface::class, CheckoutPaymentGateway::class);
        $this->app->bind(InventoryManagerInterface::class, InventoryManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
