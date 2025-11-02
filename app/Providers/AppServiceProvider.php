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

/**
 * Class AppServiceProvider
 *
 * This service provider is responsible for registering and bootstrapping
 * application-level services, such as dependency bindings for repositories.
 *
 * It follows the repository pattern to separate data access logic from business logic,
 * ensuring cleaner and more maintainable code across the application.
 *
 * @package App\Providers
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * This method is used to bind repository interfaces to their concrete implementations
     * and dynamically register repositories that don’t have specific interfaces.
     *
     * It ensures that dependency injection automatically resolves
     * the correct repository classes throughout the application.
     *
     * @return void
     */
    public function register(): void
    {
        /**
         * Bind specific repository interfaces to their implementations.
         * 
         * @example
         * When the container resolves UserRepositoryInterface,
         * it will automatically inject an instance of UserRepository.
         */
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);

        /**
         * Define repositories that don’t have dedicated interfaces.
         *
         * The array maps each repository class to its corresponding Eloquent model.
         * This allows dynamic registration using the singleton pattern below.
         *
         * @var array<class-string, class-string>
         */
        $repositories = [
            OrderRepository::class => \App\Models\Order::class,
            OrderItemRepository::class => \App\Models\OrderItem::class,
            ProductRepository::class => \App\Models\Product::class,
        ];

        /**
         * Register all repositories dynamically as singletons.
         *
         * @param string $repository The repository class name.
         * @param string $model The model class name.
         * @return void
         */
        foreach ($repositories as $repository => $model) {
            $this->app->singleton($repository, fn() => new $repository(new $model()));
        }
    }

    /**
     * Bootstrap any application services.
     *
     * This method runs after all services have been registered.
     * You can use it to perform any application bootstrapping tasks,
     * such as publishing config files or extending Laravel core services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Reserved for future application bootstrapping logic.
    }
}
