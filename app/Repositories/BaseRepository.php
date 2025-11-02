<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class BaseRepository
 *
 * Provides a base implementation for common CRUD operations.
 * All repositories can extend this class to reuse and standardize data logic.
 *
 * @package App\Repositories
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * The model instance for the repository.
     *
     * @var Model
     */
    protected Model $model;

    /**
     * BaseRepository constructor.
     *
     * @param Model $model Injected model instance
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Retrieve all records.
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Retrieve paginated records.
     *
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    /**
     * Find a record by its ID.
     *
     * @param int $id Record ID
     * @return Model|null
     */
    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * Create a new record.
     *
     * @param array $data Record attributes
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing record.
     *
     * @param Model $model Record instance to update
     * @param array $data Updated attributes
     * @return bool
     */
    public function update(Model $model, array $data): bool
    {
        return $model->update($data);
    }

    /**
     * Delete a record from the database.
     *
     * @param Model $model Record instance to delete
     * @return bool
     */
    public function delete(Model $model): bool
    {
        return $model->delete();
    }
}
