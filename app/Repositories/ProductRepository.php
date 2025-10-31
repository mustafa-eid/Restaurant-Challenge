<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * Get all products with pagination.
     *
     * Eager loading can be added here if you want relationships (like orderItems).
     *
     * @return LengthAwarePaginator Paginated products
     */
    public function getAll(): LengthAwarePaginator
    {
        return Product::orderByDesc('id')->paginate(10);
    }

    /**
     * Store a new product in the database.
     *
     * @param array $data Product attributes
     * @return Product Newly created product
     */
    public function store(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Find a product by its ID.
     *
     * @param int $id Product ID
     * @return Product|null Product model or null if not found
     */
    public function findById(int $id): ?Product
    {
        return Product::find($id);
    }

    /**
     * Update an existing product.
     *
     * @param Product $product Product model
     * @param array $data Updated attributes
     * @return bool True if update succeeds, false otherwise
     */
    public function update(Product $product, array $data): bool
    {
        return $product->update($data);
    }

    /**
     * Delete a product from the database.
     *
     * @param Product $product Product model
     * @return bool True if deletion succeeds, false otherwise
     */
    public function delete(Product $product): bool
    {
        return $product->delete();
    }
}
