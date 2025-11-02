<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface RepositoryInterface
 *
 * Defines a standard contract for all repository classes.
 * Provides common CRUD operations to ensure consistency and reusability
 * across the application data access layer.
 *
 * @package App\Repositories\Contracts
 */
interface RepositoryInterface
{
    /**
     * Retrieve all records.
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Retrieve records with pagination.
     *
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 10): LengthAwarePaginator;

    /**
     * Find a specific record by its ID.
     *
     * @param int $id Record ID
     * @return Model|null
     */
    public function find(int $id): ?Model;

    /**
     * Create a new record in the database.
     *
     * @param array $data Attributes for creation
     * @return Model
     */
    public function create(array $data): Model;

    /**
     * Update an existing record.
     *
     * @param Model $model The model instance to update
     * @param array $data Updated attributes
     * @return bool
     */
    public function update(Model $model, array $data): bool;

    /**
     * Delete a record from the database.
     *
     * @param Model $model The model instance to delete
     * @return bool
     */
    public function delete(Model $model): bool;
}
