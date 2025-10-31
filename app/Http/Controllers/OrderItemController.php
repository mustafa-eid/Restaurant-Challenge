<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderItemRequest;
use App\Http\Requests\UpdateOrderItemRequest;
use App\Http\Resources\OrderItemResource;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponseTrait;
use Exception;

/**
 * Class OrderItemController
 *
 * This controller manages individual Order Items (products associated with an order).
 * It provides full CRUD operations via a clean repository layer to ensure separation of concerns.
 *
 * Responsibilities:
 * - Handle creation, retrieval, updating, and deletion of order items.
 * - Utilize the repository pattern to abstract database logic.
 * - Provide structured JSON responses for API consumers.
 * - Support relationships with Order and Product models for enriched API output.
 */
class OrderItemController extends Controller
{
    use ApiResponseTrait;

    /**
     * Repository instance for handling data access logic.
     *
     * @var OrderItemRepositoryInterface
     */
    protected OrderItemRepositoryInterface $orderItemRepository;

    /**
     * Inject OrderItemRepositoryInterface via constructor dependency injection.
     *
     * @param OrderItemRepositoryInterface $orderItemRepository
     */
    public function __construct(OrderItemRepositoryInterface $orderItemRepository)
    {
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * Display a list of all order items.
     *
     * @return JsonResponse
     *
     * Example: GET /api/order-items
     *
     * Response includes related order and product details using Resource transformation.
     */
    public function index(): JsonResponse
    {
        // Fetch all order items (could be paginated or filtered depending on repository implementation)
        $items = $this->orderItemRepository->all();

        // Transform data using Resource for consistent JSON structure
        return $this->successResponse(
            OrderItemResource::collection($items),
            'Order items retrieved successfully.'
        );
    }

    /**
     * Store a newly created order item in storage.
     *
     * @param StoreOrderItemRequest $request
     * @return JsonResponse
     *
     * Example Payload:
     * {
     *   "order_id": 1,
     *   "product_id": 3,
     *   "quantity": 2,
     *   "price": 120.50
     * }
     */
    public function store(StoreOrderItemRequest $request): JsonResponse
    {
        try {
            // Create a new order item via repository (validations handled in FormRequest)
            $item = $this->orderItemRepository->create($request->validated());

            // Return detailed data with eager-loaded relationships for API completeness
            return $this->successResponse(
                new OrderItemResource($item->load(['order', 'product'])),
                'Order item created successfully.',
                201
            );

        } catch (Exception $e) {
            // Capture and return any unexpected errors
            return $this->errorResponse('Failed to create order item.', $e->getMessage());
        }
    }

    /**
     * Display a specific order item by its ID.
     *
     * @param int $id
     * @return JsonResponse
     *
     * Example: GET /api/order-items/5
     */
    public function show(int $id): JsonResponse
    {
        $item = $this->orderItemRepository->find($id);

        if (!$item) {
            return $this->notFoundResponse('Order item not found.');
        }

        // Wrap response in a Resource to include relational data cleanly
        return $this->successResponse(
            new OrderItemResource($item),
            'Order item retrieved successfully.'
        );
    }

    /**
     * Update an existing order item.
     *
     * @param UpdateOrderItemRequest $request
     * @param int $orderItem
     * @return JsonResponse
     *
     * Example Payload:
     * {
     *   "quantity": 3,
     *   "price": 110.00
     * }
     *
     * Example: PUT /api/order-items/7
     */
    public function update(UpdateOrderItemRequest $request, int $orderItem): JsonResponse
    {
        try {
            // Retrieve the target order item
            $item = $this->orderItemRepository->find($orderItem);

            if (!$item) {
                return $this->notFoundResponse('Order item not found.');
            }

            // Update with validated data
            $this->orderItemRepository->update($item, $request->validated());

            // Use fresh() to reload updated relationships for an accurate API response
            return $this->successResponse(
                new OrderItemResource($item->fresh(['order', 'product'])),
                'Order item updated successfully.'
            );

        } catch (Exception $e) {
            return $this->errorResponse('Failed to update order item.', $e->getMessage());
        }
    }

    /**
     * Remove an order item from storage.
     *
     * @param int $orderItem
     * @return JsonResponse
     *
     * Example: DELETE /api/order-items/9
     */
    public function destroy(int $orderItem): JsonResponse
    {
        try {
            // Locate the order item before attempting deletion
            $item = $this->orderItemRepository->find($orderItem);

            if (!$item) {
                return $this->notFoundResponse('Order item not found.');
            }

            // Perform soft or hard delete (depends on repository implementation)
            $this->orderItemRepository->delete($item);

            return $this->successResponse(null, 'Order item deleted successfully.');

        } catch (Exception $e) {
            return $this->errorResponse('Failed to delete order item.', $e->getMessage());
        }
    }
}
