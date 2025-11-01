<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Services\Contracts\InventoryManagerInterface;
use App\Services\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class OrderService
 *
 * Business logic for handling order placement and related side effects.
 */
class OrderService
{
    private PaymentGatewayInterface $paymentGateway;
    private InventoryManagerInterface $inventoryManager;

    public function __construct(
        PaymentGatewayInterface $paymentGateway,
        InventoryManagerInterface $inventoryManager
    ) {
        $this->paymentGateway = $paymentGateway;
        $this->inventoryManager = $inventoryManager;
    }

    /**
     * Place or update an order and perform side effects (payment, inventory).
     *
     * @param Order $order
     * @param bool $isUpdate If true, this is an update operation.
     * @return bool True if processed successfully, false otherwise.
     *
     * @throws Throwable Re-throws unexpected exceptions after rolling back.
     */
    public function placeOrder(Order $order, bool $isUpdate = false): bool
    {
        DB::beginTransaction();

        try {
            // Calculate total amount
            $total = $this->calculateOrderTotal($order);
            $order->total_amount = $total;

            // Only perform payment/inventory when there are items and total > 0
            $hasItems = $order->items && $order->items->isNotEmpty();

            if ($hasItems && $total > 0.0) {
                // Process payment
                $paymentSuccess = $this->paymentGateway->processPayment($total);

                if (! $paymentSuccess) {
                    // Do not commit; roll back and return false so caller can handle.
                    DB::rollBack();
                    Log::warning('Payment failed during order placement.', ['order_id' => $order->id ?? null, 'total' => $total]);
                    return false;
                }

                // Prepare inventory payload
                $inventoryItems = $order->items->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'quantity' => (int) $item->quantity,
                    ];
                })->toArray();

                $inventorySuccess = $this->inventoryManager->updateInventoryBatch($inventoryItems);

                if (! $inventorySuccess) {
                    DB::rollBack();
                    Log::warning('Inventory update failed after payment.', ['order_id' => $order->id ?? null]);
                    return false;
                }
            } else {
                Log::info('Order has no items or zero total; skipping payment/inventory.', ['order_id' => $order->id ?? null]);
            }

            // Persist order (create/update)
            $order->save();

            DB::commit();

            Log::info('Order processed successfully.', [
                'order_id' => $order->id,
                'total' => $order->total_amount,
            ]);

            return true;
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Order processing exception.', [
                'order_id' => $order->id ?? null,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to let higher layers (controllers/jobs) decide how to handle.
            throw $e;
        }
    }

    /**
     * Calculate total amount for the given order.
     *
     * @param Order $order
     * @return float
     */
    private function calculateOrderTotal(Order $order): float
    {
        if (! $order->items || $order->items->isEmpty()) {
            return 0.0;
        }

        $total = (float) $order->items->sum(fn($item) => (float) $item->price * (int) $item->quantity);

        // Optionally round to 2 decimals
        return round($total, 2);
    }
}
