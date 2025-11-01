<?php

declare(strict_types=1);

namespace App\Services\Contracts;

/**
 * Interface InventoryManagerInterface
 *
 * Contract for updating inventory in bulk.
 */
interface InventoryManagerInterface
{
    /**
     * Update inventory for a batch of items.
     *
     * Each item is expected to be an associative array containing at least:
     *  - product_id (int|string)
     *  - quantity (int)
     *
     * @param array<int, array<string, mixed>> $items
     * @return bool True on success, false on failure.
     */
    public function updateInventoryBatch(array $items): bool;
}
