<?php

namespace App\Repositories\Interfaces;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    /**
     * Retrieve all products with pagination.
     *
     * @return LengthAwarePaginator Paginated collection of products
     */
    public function getAll(): LengthAwarePaginator;

    /**
     * Store a new product in the database.
     *
     * @param array $data Product attributes
     * @return Product Newly created product
     */
    public function store(array $data): Product;

    /**
     * Find a product by its ID.
     *
     * @param int $id Product ID
     * @return Product|null Product model or null if not found
     */
    public function findById(int $id): ?Product;

    /**
     * Update an existing product.
     *
     * @param Product $product Product model
     * @param array $data Updated attributes
     * @return bool True if update succeeds, false otherwise
     */
    public function update(Product $product, array $data): bool;

    /**
     * Delete a product.
     *
     * @param Product $product Product model
     * @return bool True if deletion succeeds, false otherwise
     */
    public function delete(Product $product): bool;
}
