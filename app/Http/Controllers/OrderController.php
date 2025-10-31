<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;
use Exception;

/**
 * Class OrderController
 *
 * This controller handles the management of orders including:
 * - Creating new orders with multiple order items.
 * - Listing orders with their related data (branch + products).
 * - Updating existing orders (including full replacement of items).
 * - Deleting orders along with their associated items.
 *
 * The controller ensures database integrity using transactions and
 * returns consistent JSON responses through ApiResponseTrait.
 */
class OrderController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a paginated list of all orders with their related items and branches.
     *
     * @return JsonResponse
     *
     * Example: GET /api/orders
     */
    public function index(): JsonResponse
    {
        // Retrieve orders with eager-loaded relationships for performance
        $orders = Order::with(['items.product', 'branch'])
            ->orderByDesc('id') // Most recent orders first
            ->paginate(10);

        return $this->successResponse($orders, 'Orders retrieved successfully.');
    }

    /**
     * Store a new order with its associated items.
     *
     * Transactional integrity is enforced: if any part fails (e.g. item creation),
     * the entire order creation will be rolled back.
     *
     * @param StoreOrderRequest $request
     * @return JsonResponse
     *
     * Example Payload:
     * {
     *   "branch_id": 1,
     *   "name": "Order A",
     *   "items": [
     *     { "product_id": 2, "price": 120.50, "quantity": 3 },
     *     { "product_id": 5, "price": 50, "quantity": 1 }
     *   ]
     * }
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Create base order record (without total yet)
            $order = Order::create([
                'branch_id' => $request->branch_id,
                'name' => $request->name,
                'total_amount' => 0
            ]);

            $total = 0;

            // Create associated order items and compute total
            if ($request->has('items') && is_array($request->items)) {
                foreach ($request->items as $item) {
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;

                    // Persist each order item
                    $order->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity'   => $item['quantity'],
                        'price'      => $item['price'],
                    ]);
                }
            }

            // Update order total after items are inserted
            $order->update(['total_amount' => $total]);

            DB::commit();

            // Reload related models for complete response
            $order->load(['items.product', 'branch']);

            return $this->successResponse($order, 'Order created successfully.', 201);

        } catch (Exception $e) {
            DB::rollBack();
            // Ensure client receives meaningful error with details (in dev mode)
            return $this->errorResponse('Failed to create order.', $e->getMessage());
        }
    }

    /**
     * Display a single order by its ID, including items and branch.
     *
     * @param int $id
     * @return JsonResponse
     *
     * Example: GET /api/orders/5
     */
    public function show($id): JsonResponse
    {
        $order = Order::with(['items.product', 'branch'])->find($id);

        if (!$order) {
            return $this->notFoundResponse('Order not found.');
        }

        return $this->successResponse($order, 'Order retrieved successfully.');
    }

    /**
     * Update an existing order and optionally replace its items.
     *
     * This method removes all previous items if new ones are provided,
     * ensuring total consistency with the new payload.
     * 
     * The total amount is recalculated from all items.
     *
     * @param UpdateOrderRequest $request
     * @param Order $order
     * @return JsonResponse
     *
     * Example Payload:
     * {
     *   "branch_id": 2,
     *   "name": "Updated Order X",
     *   "items": [
     *     { "product_id": 3, "price": 60, "quantity": 2 }
     *   ]
     * }
     */
    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Update basic order fields (keep existing if not sent)
            $order->update([
                'branch_id' => $request->branch_id ?? $order->branch_id,
                'name' => $request->name ?? $order->name,
            ]);

            $total = 0;

            // If items are passed, replace all existing ones
            if ($request->has('items') && is_array($request->items)) {
                $order->items()->delete();

                foreach ($request->items as $item) {
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;

                    $order->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity'   => $item['quantity'],
                        'price'      => $item['price'],
                    ]);
                }
            } else {
                // If no items provided, maintain current total (recalculate to ensure accuracy)
                $total = $order->items()->sum(DB::raw('price * quantity'));
            }

            // Update total amount field
            $order->update(['total_amount' => $total]);

            DB::commit();

            $order->load(['items.product', 'branch']);

            return $this->successResponse($order, 'Order updated successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update order.', $e->getMessage());
        }
    }

    /**
     * Delete an order and all its associated items.
     *
     * Cascade delete is not used intentionally to maintain explicit control.
     *
     * @param Order $order
     * @return JsonResponse
     *
     * Example: DELETE /api/orders/5
     */
    public function destroy(Order $order): JsonResponse
    {
        try {
            // Manually remove order items first for data integrity
            $order->items()->delete();

            // Then delete the order record itself
            $order->delete();

            return $this->successResponse(null, 'Order deleted successfully.');
        } catch (Exception $e) {
            return $this->errorResponse('Failed to delete order.', $e->getMessage());
        }
    }
}
