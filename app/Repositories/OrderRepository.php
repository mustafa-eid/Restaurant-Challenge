<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * Get all orders with pagination and relations.
     */
    public function all(int $perPage = 50): LengthAwarePaginator
    {
        return Order::with(['items', 'branch'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find a specific order by its ID.
     */
    public function find(int $id): ?Order
    {
        return Order::with(['items', 'branch'])->find($id);
    }

    /**
     * Create a new order.
     */
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    /**
     * Update an existing order.
     */
    public function update(Order $order, array $data): bool
    {
        return $order->update($data);
    }

    /**
     * Delete an order.
     */
    public function delete(Order $order): bool
    {
        return $order->delete();
    }
}
