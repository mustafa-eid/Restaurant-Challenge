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
 * Business logic for handling order placement and related side effects.
 *
 * Note: OrderService is responsible for DB transaction boundaries for the
 * complete order flow (payment + inventory + saving order).
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
            // Calculate and set total amount
            $total = $this->calculateOrderTotal($order);
            $order->total_amount = $total;

            $hasItems = $order->items && $order->items->isNotEmpty();

            if ($hasItems && $total > 0.0) {
                // Process payment
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

                // Prepare inventory payload
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

            // Persist order (create/update)
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

            // Re-throw so higher layers can handle the exceptional path.
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

        // Keep currency rounding consistent
        return round($total, 2);
    }
}
