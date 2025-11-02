<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Services\Contracts\InventoryManagerInterface;
use App\Services\Contracts\PaymentGatewayInterface;
use App\Services\Payment\PaymentResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class OrderService
 *
 * Handles business logic related to order placement, including
 * payment processing, inventory updates, and database transactions.
 *
 * @package App\Services
 */
class OrderService
{
    /**
     * Payment gateway implementation.
     *
     * @var PaymentGatewayInterface
     */
    private PaymentGatewayInterface $paymentGateway;

    /**
     * Inventory manager implementation.
     *
     * @var InventoryManagerInterface
     */
    private InventoryManagerInterface $inventoryManager;

    /**
     * OrderService constructor.
     *
     * @param PaymentGatewayInterface $paymentGateway Payment gateway service for processing payments.
     * @param InventoryManagerInterface $inventoryManager Inventory manager service for updating stock levels.
     */
    public function __construct(
        PaymentGatewayInterface $paymentGateway,
        InventoryManagerInterface $inventoryManager
    ) {
        $this->paymentGateway = $paymentGateway;
        $this->inventoryManager = $inventoryManager;
    }

    /**
     * Place or update an order and execute side effects (payment, inventory updates).
     *
     * This method encapsulates a full transactional flow:
     *  - Calculates total order amount
     *  - Processes payment (if applicable)
     *  - Updates inventory quantities
     *  - Persists the order in the database
     *
     * @param Order $order The order model instance being processed.
     * @param bool $isUpdate Indicates whether the order is being updated or newly created.
     *
     * @return bool True if the operation completes successfully; false if payment or inventory update fails.
     *
     * @throws Throwable Rethrows unexpected exceptions after rollback for higher-level handling.
     */
    public function placeOrder(Order $order, bool $isUpdate = false): bool
    {
        DB::beginTransaction();

        try {
            // Step 1: Calculate and assign total amount
            $total = $this->calculateOrderTotal($order);
            $order->total_amount = $total;

            $hasItems = $order->items && $order->items->isNotEmpty();

            if ($hasItems && $total > 0.0) {
                // Step 2: Process payment
                /** @var PaymentResult $paymentResult */
                $paymentResult = $this->paymentGateway->processPayment($total);

                if (! $paymentResult->success) {
                    DB::rollBack();
                    Log::warning('[OrderService] Payment failed during order placement.', [
                        'order_id' => $order->id ?? null,
                        'total' => $total,
                        'payment_message' => $paymentResult->message,
                    ]);

                    return false;
                }

                // Step 3: Update inventory
                $inventoryItems = $order->items->map(fn($item): array => [
                    'product_id' => $item->product_id,
                    'quantity' => (int) $item->quantity,
                ])->toArray();

                $inventorySuccess = $this->inventoryManager->updateInventoryBatch($inventoryItems);

                if (! $inventorySuccess) {
                    DB::rollBack();
                    Log::warning('[OrderService] Inventory update failed after payment.', [
                        'order_id' => $order->id ?? null,
                    ]);
                    return false;
                }
            } else {
                Log::info('[OrderService] Order has no items or zero total; skipping payment/inventory.', [
                    'order_id' => $order->id ?? null,
                ]);
            }

            // Step 4: Save order
            $order->save();

            DB::commit();

            Log::info('[OrderService] Order processed successfully.', [
                'order_id' => $order->id,
                'total' => $order->total_amount,
            ]);

            return true;
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('[OrderService] Exception while processing order.', [
                'order_id' => $order->id ?? null,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Calculate the total amount for the given order.
     *
     * Iterates through order items to compute total value based on
     * each item's price and quantity.
     *
     * @param Order $order The order whose total amount should be calculated.
     * @return float The calculated total amount, rounded to two decimal places.
     */
    private function calculateOrderTotal(Order $order): float
    {
        if (! $order->items || $order->items->isEmpty()) {
            return 0.0;
        }

        $total = (float) $order->items->sum(
            fn($item) => (float) $item->price * (int) $item->quantity
        );

        return round($total, 2);
    }
}
