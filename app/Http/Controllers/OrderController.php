<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

/**
 * Class OrderController
 *
 * Handles all CRUD operations for orders.
 * Provides endpoints for listing, creating, retrieving, updating, and deleting orders.
 *
 * @package App\Http\Controllers
 */
class OrderController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a paginated list of orders with their items.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $orders = Order::with('items')->paginate(10);
        return $this->successResponse(OrderResource::collection($orders), 'Orders retrieved successfully.');
    }

    /**
     * Store a newly created order.
     *
     * @param  StoreOrderRequest  $request
     * @return JsonResponse
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = Order::create($request->validated());
        return $this->successResponse(new OrderResource($order), 'Order created successfully.', 201);
    }

    /**
     * Display a specific order by ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $order = Order::with('items')->find($id);

        if (!$order) {
            return $this->notFoundResponse('Order not found.');
        }

        return $this->successResponse(new OrderResource($order), 'Order retrieved successfully.');
    }

    /**
     * Update an existing order by ID.
     *
     * @param  UpdateOrderRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(UpdateOrderRequest $request, $id): JsonResponse
    {
        $order = Order::find($id);

        if (!$order) {
            return $this->notFoundResponse('Order not found.');
        }

        $order->update($request->validated());

        return $this->successResponse(new OrderResource($order), 'Order updated successfully.');
    }

    /**
     * Delete an order by ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $order = Order::find($id);

        if (!$order) {
            return $this->notFoundResponse('Order not found.');
        }

        $order->delete();

        return $this->successResponse(null, 'Order deleted successfully.');
    }
}
