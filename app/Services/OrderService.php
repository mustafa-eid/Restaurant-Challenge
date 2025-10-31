<?php

namespace App\Services;

use App\Models\Order;
use App\Services\Contracts\PaymentGatewayInterface;
use App\Services\Contracts\InventoryManagerInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

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
     * Handle order creation or update.
     *
     * @param Order $order
     * @param bool $isUpdate
     * @return void
     * @throws Exception
     */
    public function placeOrder(Order $order, bool $isUpdate = false): void
    {
        DB::beginTransaction();

        try {
            // If order has items, calculate total and handle payment/inventory
            if ($order->items && $order->items->isNotEmpty()) {
                // Calculate total amount dynamically
                $order->total_amount = $order->items->sum(
                    fn($item) => $item->price * $item->quantity
                );

                // Process payment & update inventory only on create or item changes
                if (!$isUpdate || $order->wasChanged('items')) {
                    $this->paymentGateway->processPayment($order->total_amount);
                    $this->inventoryManager->updateInventoryBatch(
                        $order->items->map(fn($item) => [
                            'product_id' => $item->product_id,
                            'quantity'   => $item->quantity,
                        ])->toArray()
                    );
                }
            } else {
                // If no items exist, skip payment & inventory
                Log::info('Order created without items (pending items addition)', [
                    'order_id' => $order->id
                ]);
            }

            $order->save();

            DB::commit();
            Log::info('Order processed successfully', ['order_id' => $order->id]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Order processing failed', [
                'order_id' => $order->id ?? null,
                'error'    => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
