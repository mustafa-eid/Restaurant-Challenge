<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Class ProductRepository
 *
 * Responsible for interacting with the Product model.
 * Leverages BaseRepository for shared logic and
 * provides additional customization for paginated retrieval.
 *
 * @package App\Repositories
 */
class ProductRepository extends BaseRepository
{
    /**
     * ProductRepository constructor.
     *
     * @param Product $model Injected Product model instance
     */
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    /**
     * Retrieve all products ordered by ID in descending order with pagination.
     *
     * @return LengthAwarePaginator
     */
    public function getAll(): LengthAwarePaginator
    {
        return $this->model->orderByDesc('id')->paginate(10);
    }
}
