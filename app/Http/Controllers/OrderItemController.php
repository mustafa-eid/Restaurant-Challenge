<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderItemRequest;
use App\Http\Requests\UpdateOrderItemRequest;
use App\Http\Resources\OrderItemResource;
use App\Models\OrderItem;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class OrderItemController extends Controller
{
    use ApiResponseTrait;

    public function index(): JsonResponse
    {
        $items = OrderItem::with(['product', 'order'])->paginate(10);
        return $this->successResponse(OrderItemResource::collection($items), 'Order items retrieved successfully.');
    }

    public function store(StoreOrderItemRequest $request): JsonResponse
    {
        $item = OrderItem::create($request->validated());
        return $this->successResponse(new OrderItemResource($item), 'Order item created successfully.', 201);
    }

    public function show($id): JsonResponse
    {
        $orderItem = OrderItem::with(['product', 'order'])->find($id);

        if (!$orderItem) {
            return $this->notFoundResponse('Order item not found.');
        }

        return $this->successResponse(new OrderItemResource($orderItem), 'Order item retrieved successfully.');
    }

    public function update(UpdateOrderItemRequest $request, $id): JsonResponse
    {
        $orderItem = OrderItem::find($id);

        if (!$orderItem) {
            return $this->notFoundResponse('Order item not found.');
        }

        $orderItem->update($request->validated());

        return $this->successResponse(new OrderItemResource($orderItem), 'Order item updated successfully.');
    }

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
