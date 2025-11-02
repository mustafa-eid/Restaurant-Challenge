<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Repositories
use App\Repositories\{
    UserRepository,
    OrderRepository,
    OrderItemRepository,
    ProductRepository
};

// Interfaces
use App\Repositories\Interfaces\UserRepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind repository interfaces
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);

        // List of repositories that don’t have interfaces
        $repositories = [
            OrderRepository::class => \App\Models\Order::class,
            OrderItemRepository::class => \App\Models\OrderItem::class,
            ProductRepository::class => \App\Models\Product::class,
        ];

        // Register all repositories dynamically
        foreach ($repositories as $repository => $model) {
            $this->app->singleton($repository, fn() => new $repository(new $model()));
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
