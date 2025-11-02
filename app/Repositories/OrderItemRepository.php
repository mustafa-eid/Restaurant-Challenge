<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class OrderItemRepository
 *
 * Provides data access logic for OrderItem model.
 * Extends BaseRepository for CRUD operations and adds
 * advanced eager-loading logic for performance optimization.
 *
 * @package App\Repositories
 */
class OrderItemRepository extends BaseRepository
{
    /**
     * OrderItemRepository constructor.
     *
     * @param OrderItem $model Injected OrderItem model instance
     */
    public function __construct(OrderItem $model)
    {
        parent::__construct($model);
    }

    /**
     * Retrieve all order items with related order and product models.
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->model
            ->select('id', 'order_id', 'product_id', 'quantity', 'price', 'created_at')
            ->with(['order', 'product'])
            ->latest()
            ->get();
    }
}
