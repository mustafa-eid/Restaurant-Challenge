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
 */
class InventoryManager implements InventoryManagerInterface
{
    /**
     * Update inventory in a single transaction for the given items.
     *
     * @param array<int, array<string, mixed>> $items
     * @return bool
     */
    public function updateInventoryBatch(array $items): bool
    {
        if (empty($items)) {
            return true;
        }

        try {
            DB::beginTransaction();

            // Example logic: decrement stock by quantity for each product_id
            foreach ($items as $item) {
                $productId = $item['product_id'] ?? null;
                $quantity = (int) ($item['quantity'] ?? 0);

                if ($productId === null || $quantity <= 0) {
                    // Skip invalid entries but log them
                    Log::warning('InventoryManager skipping invalid item', $item);
                    continue;
                }

                // Use DB::table for a lightweight update (adapt to your Product model schema)
                DB::table('products')
                    ->where('id', $productId)
                    ->where('available', '>=', $quantity)
                    ->decrement('available', $quantity);
            }

            DB::commit();

            Log::info('Inventory batch update succeeded.', ['count' => count($items)]);

            return true;
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Inventory batch update failed.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }
}
