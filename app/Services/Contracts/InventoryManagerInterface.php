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
     * Behavior:
     *  - The implementation SHOULD be called inside a DB transaction if row-level locks
     *    are required (e.g. lockForUpdate).
     *  - Returns true on full success (all items decremented), false if any item
     *    could not be decremented due to insufficient stock or other recoverable reason.
     *
     * @param array<int, array<string, mixed>> $items
     * @return bool True on success, false on failure (e.g. insufficient stock)
     */
    public function updateInventoryBatch(array $items): bool;
}
