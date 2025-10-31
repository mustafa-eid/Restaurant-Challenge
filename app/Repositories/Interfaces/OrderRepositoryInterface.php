<?php

namespace App\Repositories\Interfaces;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    /**
     * Retrieve all orders with pagination.
     *
     * This method defines a contract for fetching a paginated list of orders.
     * Implementing classes should handle eager loading of related models if necessary.
     *
     * @param int $perPage Number of orders per page (default 50)
     * @return LengthAwarePaginator Paginated collection of orders
     */
    public function all(int $perPage = 50): LengthAwarePaginator;

    /**
     * Find a specific order by its ID.
     *
     * This method defines a contract for retrieving a single order.
     * Implementing classes should return null if the order does not exist.
     *
     * @param int $id Order ID
     * @return Order|null The order model or null if not found
     */
    public function find(int $id): ?Order;

    /**
     * Create a new order.
     *
     * This method defines a contract for creating a new order in the database.
     *
     * @param array $data Attributes required to create the order
     * @return Order The newly created order model
     */
    public function create(array $data): Order;

    /**
     * Update an existing order.
     *
     * This method defines a contract for updating an existing order.
     * Implementing classes should return true if the update succeeds.
     *
     * @param Order $order The order model to update
     * @param array $data Attributes to update
     * @return bool True if the update was successful, false otherwise
     */
    public function update(Order $order, array $data): bool;

    /**
     * Delete an order.
     *
     * This method defines a contract for removing an order from the database.
     * Implementing classes should return true if the deletion succeeds.
     *
     * @param Order $order The order model to delete
     * @return bool True if the deletion was successful, false otherwise
     */
    public function delete(Order $order): bool;
}
