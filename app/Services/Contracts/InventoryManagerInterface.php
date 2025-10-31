<?php

namespace App\Services\Contracts;

interface InventoryManagerInterface
{
    public function updateInventoryBatch(array $items): bool;
}
