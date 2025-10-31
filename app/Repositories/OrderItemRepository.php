<?php

namespace App\Repositories;

use App\Models\OrderItem;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class OrderItemRepository implements OrderItemRepositoryInterface
{
    /**
     * Get all order items.
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return OrderItem::query()
            ->select('id', 'order_id', 'product_id', 'quantity', 'price', 'created_at')
            ->with(['order', 'product'])
            ->latest()
            ->get();
    }

    /**
     * Find a specific order item by ID.
     *
     * @param int $id
     * @return OrderItem|null
     */
    public function find(int $id): ?OrderItem
    {
        return OrderItem::with(['order', 'product'])->find($id);
    }

    /**
     * Create a new order item.
     *
     * @param array $data
     * @return OrderItem
     */
    public function create(array $data): OrderItem
    {
        return OrderItem::create($data);
    }

    /**
     * Update an existing order item.
     *
     * @param OrderItem $orderItem
     * @param array $data
     * @return bool
     */
    public function update(OrderItem $orderItem, array $data): bool
    {
        return $orderItem->update($data);
    }

    /**
     * Delete an order item.
     *
     * @param OrderItem $orderItem
     * @return bool
     */
    public function delete(OrderItem $orderItem): bool
    {
        return $orderItem->delete();
    }
}
