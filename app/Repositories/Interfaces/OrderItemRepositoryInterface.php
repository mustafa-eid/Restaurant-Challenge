<?php

namespace App\Repositories\Interfaces;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Collection;

interface OrderItemRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?OrderItem;

    public function create(array $data): OrderItem;

    public function update(OrderItem $orderItem, array $data): bool;

    public function delete(OrderItem $orderItem): bool;
}
