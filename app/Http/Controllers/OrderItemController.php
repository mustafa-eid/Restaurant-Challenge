<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderItemRequest;
use App\Http\Requests\UpdateOrderItemRequest;
use App\Http\Resources\OrderItemResource;
use App\Models\OrderItem;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

/**
 * Class OrderItemController
 *
 * Manages CRUD operations for order items.
 * Handles creation, retrieval, updating, and deletion of order item records.
 *
 * @package App\Http\Controllers
 */
class OrderItemController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a paginated list of order items with related order and product data.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $items = OrderItem::with(['product', 'order'])->paginate(10);
        return $this->successResponse(OrderItemResource::collection($items), 'Order items retrieved successfully.');
    }

    /**
     * Store a new order item.
     *
     * @param  StoreOrderItemRequest  $request
     * @return JsonResponse
     */
    public function store(StoreOrderItemRequest $request): JsonResponse
    {
        $item = OrderItem::create($request->validated());
        return $this->successResponse(new OrderItemResource($item), 'Order item created successfully.', 201);
    }

    /**
     * Display a specific order item by ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $orderItem = OrderItem::with(['product', 'order'])->find($id);

        if (!$orderItem) {
            return $this->notFoundResponse('Order item not found.');
        }

        return $this->successResponse(new OrderItemResource($orderItem), 'Order item retrieved successfully.');
    }

    /**
     * Update an existing order item.
     *
     * @param  UpdateOrderItemRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(UpdateOrderItemRequest $request, $id): JsonResponse
    {
        $orderItem = OrderItem::find($id);

        if (!$orderItem) {
            return $this->notFoundResponse('Order item not found.');
        }

        $orderItem->update($request->validated());

        return $this->successResponse(new OrderItemResource($orderItem), 'Order item updated successfully.');
    }

    /**
     * Delete an order item by ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $orderItem = OrderItem::find($id);

        if (!$orderItem) {
            return $this->notFoundResponse('Order item not found.');
        }

        $orderItem->delete();

        return $this->successResponse(null, 'Order item deleted successfully.');
    }
}
