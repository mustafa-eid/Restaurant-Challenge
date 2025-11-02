<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Services\Contracts\InventoryManagerInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class InventoryManager
 *
 * Concrete implementation for inventory updates.
 *
 * Notes:
 *  - This implementation expects to be called inside a DB transaction when used
 *    in contexts that require row locking (it uses lockForUpdate()).
 *  - It performs a single bulk UPDATE (CASE WHEN ...) after validating availability.
 */
class InventoryManager implements InventoryManagerInterface
{
    /**
     * Update inventory for a batch of items.
     *
     * @param array<int, array<string, mixed>> $items
     * @return bool True on success, false if any item has insufficient stock or on failure.
     */
    public function updateInventoryBatch(array $items): bool
    {
        if (empty($items)) {
            return true;
        }

        // Normalize items by product_id and accumulate quantities if duplicates provided
        $qtyByProduct = [];
        foreach ($items as $item) {
            $productId = $item['product_id'] ?? null;
            $quantity = (int) ($item['quantity'] ?? 0);

            if ($productId === null || $quantity <= 0) {
                Log::warning('[InventoryManager] Skipping invalid item payload', (array) $item);
                continue;
            }

            if (! isset($qtyByProduct[$productId])) {
                $qtyByProduct[$productId] = 0;
            }

            $qtyByProduct[$productId] += $quantity;
        }

        if (empty($qtyByProduct)) {
            return true;
        }

        $ids = array_keys($qtyByProduct);

        try {
            // Acquire row locks to avoid race conditions (this requires an outer transaction).
            // If not in a transaction, lockForUpdate will still work but the scope differs.
            $rows = DB::table('products')
                ->select('id', 'available')
                ->whereIn('id', $ids)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Ensure all requested products exist and have sufficient stock
            $insufficient = [];
            foreach ($qtyByProduct as $pid => $requested) {
                $row = $rows->get($pid);
                if (! $row) {
                    $insufficient[] = [
                        'product_id' => $pid,
                        'reason' => 'not_found',
                    ];
                    continue;
                }

                $available = (int) $row->available;
                if ($available < $requested) {
                    $insufficient[] = [
                        'product_id' => $pid,
                        'available' => $available,
                        'requested' => $requested,
                    ];
                }
            }

            if (! empty($insufficient)) {
                Log::warning('[InventoryManager] Insufficient stock for some items', ['problems' => $insufficient]);
                return false;
            }

            // Build bulk update with CASE to decrement in a single query
            $cases = [];
            $bindings = [];
            foreach ($qtyByProduct as $pid => $requested) {
                $cases[] = "WHEN id = ? THEN GREATEST(available - ?, 0)";
                $bindings[] = $pid;
                $bindings[] = $requested;
            }

            $idsPlaceholders = implode(',', array_fill(0, count($ids), '?'));
            $bindings = array_merge($bindings, $ids);

            $sql = sprintf(
                'UPDATE products SET available = CASE %s ELSE available END WHERE id IN (%s)',
                implode(' ', $cases),
                $idsPlaceholders
            );

            $affected = DB::update($sql, $bindings);

            Log::info('[InventoryManager] Bulk inventory update executed.', [
                'updated_rows' => $affected,
                'items_count' => count($qtyByProduct),
            ]);

            return true;
        } catch (Throwable $e) {
            Log::error('[InventoryManager] Bulk update failed.', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }
}
