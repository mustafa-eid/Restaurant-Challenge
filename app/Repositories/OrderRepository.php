<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Class OrderRepository
 *
 * Handles all data access logic for the Order model.
 * Extends the BaseRepository for common CRUD operations,
 * and adds custom methods specific to Order business logic.
 *
 * @package App\Repositories
 */
class OrderRepository extends BaseRepository
{
    /**
     * OrderRepository constructor.
     *
     * @param Order $model Injected Order model instance
     */
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    /**
     * Retrieve all orders with related items and branches, paginated.
     *
     * @param int $perPage Number of orders per page
     * @return LengthAwarePaginator
     */
    public function allWithRelations(int $perPage = 50): LengthAwarePaginator
    {
        return $this->model
            ->with(['items', 'branch'])
            ->latest()
            ->paginate($perPage);
    }
}
