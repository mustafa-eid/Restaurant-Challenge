<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    use ApiResponseTrait;

    public function index(): JsonResponse
    {
        $orders = Order::with('items')->paginate(10);
        return $this->successResponse(OrderResource::collection($orders), 'Orders retrieved successfully.');
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = Order::create($request->validated());
        return $this->successResponse(new OrderResource($order), 'Order created successfully.', 201);
    }

    public function show($id): JsonResponse
    {
        $order = Order::with('items')->find($id);

        if (!$order) {
            return $this->notFoundResponse('Order not found.');
        }

        return $this->successResponse(new OrderResource($order), 'Order retrieved successfully.');
    }

    public function update(UpdateOrderRequest $request, $id): JsonResponse
    {
        $order = Order::find($id);

        if (!$order) {
            return $this->notFoundResponse('Order not found.');
        }

        $order->update($request->validated());

        return $this->successResponse(new OrderResource($order), 'Order updated successfully.');
    }

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
