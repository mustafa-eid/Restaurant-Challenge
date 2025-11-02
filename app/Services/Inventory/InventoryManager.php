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
 * Handles product inventory operations, such as bulk stock updates
 * after order placement or cancellation.
 *
 * This concrete implementation of {@see InventoryManagerInterface}
 * performs a transactional, atomic update of inventory quantities using
 * SQL CASE expressions for efficiency.
 *
 * **Key Features:**
 * - Ensures data integrity with row-level locking (`lockForUpdate()`).
 * - Detects insufficient stock before attempting updates.
 * - Performs updates in a single efficient SQL statement.
 *
 * @package App\Services\Inventory
 */
class InventoryManager implements InventoryManagerInterface
{
    /**
     * Update inventory for a batch of items.
     *
     * Decreases the available stock for multiple products in a single query.
     * Ensures that all products exist and have sufficient stock before applying updates.
     *
     * This method is designed to be used within an existing DB transaction.
     *
     * @param array<int, array<string, mixed>> $items
     *     A list of items, each containing:
     *     - `product_id` (int): The ID of the product.
     *     - `quantity` (int): The quantity to deduct from available stock.
     *
     * @return bool
     *     Returns `true` if the update succeeds or no valid items were given.
     *     Returns `false` if any item has insufficient stock or an exception occurs.
     */
    public function updateInventoryBatch(array $items): bool
    {
        // ✅ Early exit if no items are provided
        if (empty($items)) {
            return true;
        }

        /**
         * Normalize the input items by aggregating quantities for the same product.
         *
         * @var array<int, int> $qtyByProduct
         *     Key: product_id
         *     Value: total quantity to deduct
         */
        $qtyByProduct = [];

        foreach ($items as $item) {
            /** @var int|null $productId */
            $productId = $item['product_id'] ?? null;

            /** @var int $quantity */
            $quantity = (int) ($item['quantity'] ?? 0);

            // Skip invalid or malformed entries
            if ($productId === null || $quantity <= 0) {
                Log::warning('[InventoryManager] Skipping invalid item payload', (array) $item);
                continue;
            }

            // Accumulate quantities for duplicate product IDs
            if (! isset($qtyByProduct[$productId])) {
                $qtyByProduct[$productId] = 0;
            }

            $qtyByProduct[$productId] += $quantity;
        }

        // ✅ If all items were invalid, nothing to update
        if (empty($qtyByProduct)) {
            return true;
        }

        // Extract product IDs for querying
        $ids = array_keys($qtyByProduct);

        try {
            /**
             * Acquire database row locks on selected products.
             *
             * Using `lockForUpdate()` ensures no concurrent modifications
             * can occur while the current transaction is active.
             */
            $rows = DB::table('products')
                ->select('id', 'available')
                ->whereIn('id', $ids)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            /**
             * Validate that all requested products exist and have enough available stock.
             *
             * @var array<int, array<string, mixed>> $insufficient
             *     Contains details about unavailable or insufficient products.
             */
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
                        'available'  => $available,
                        'requested'  => $requested,
                    ];
                }
            }

            // ❌ Abort update if any product has insufficient stock
            if (! empty($insufficient)) {
                Log::warning('[InventoryManager] Insufficient stock for some items', ['problems' => $insufficient]);
                return false;
            }

            /**
             * Construct a dynamic SQL CASE expression for bulk stock updates.
             * Example:
             *   UPDATE products
             *   SET available = CASE
             *       WHEN id = 1 THEN GREATEST(available - 2, 0)
             *       WHEN id = 3 THEN GREATEST(available - 5, 0)
             *       ELSE available END
             *   WHERE id IN (1,3)
             */
            $cases = [];
            $bindings = [];

            foreach ($qtyByProduct as $pid => $requested) {
                $cases[] = "WHEN id = ? THEN GREATEST(available - ?, 0)";
                $bindings[] = $pid;
                $bindings[] = $requested;
            }

            $idsPlaceholders = implode(',', array_fill(0, count($ids), '?'));
            $bindings = array_merge($bindings, $ids);

            // Build the final SQL statement
            $sql = sprintf(
                'UPDATE products SET available = CASE %s ELSE available END WHERE id IN (%s)',
                implode(' ', $cases),
                $idsPlaceholders
            );

            // Execute the bulk update query
            $affected = DB::update($sql, $bindings);

            Log::info('[InventoryManager] Bulk inventory update executed.', [
                'updated_rows' => $affected,
                'items_count'  => count($qtyByProduct),
            ]);

            return true;
        } catch (Throwable $e) {
            /**
             * Handle unexpected exceptions during the database update.
             * Logs full error details for debugging purposes.
             */
            Log::error('[InventoryManager] Bulk update failed.', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return false;
        }
    }
}
