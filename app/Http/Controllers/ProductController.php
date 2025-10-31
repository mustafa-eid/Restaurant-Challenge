<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Exception;

/**
 * Class ProductController
 *
 * Handles all operations related to product management, including:
 * - Listing all products.
 * - Creating new products.
 * - Viewing details of a single product.
 * - Updating existing products.
 * - Deleting products.
 *
 * This controller relies on the Repository pattern for cleaner code separation and easier maintenance.
 * All responses are standardized using the ApiResponseTrait to ensure consistency across the API.
 */
class ProductController extends Controller
{
    use ApiResponseTrait;

    /**
     * The repository instance responsible for interacting with the Product model.
     *
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * Inject the ProductRepositoryInterface through constructor dependency injection.
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Display a list of all products.
     *
     * @return JsonResponse
     *
     * Example: GET /api/products
     */
    public function index(): JsonResponse
    {
        // Retrieve all products (repository may handle pagination or filters internally)
        $products = $this->productRepository->getAll();

        return $this->successResponse($products, 'Products retrieved successfully.');
    }

    /**
     * Store a newly created product in storage.
     *
     * @param StoreProductRequest $request
     * @return JsonResponse
     *
     * Example Payload:
     * {
     *   "name": "Laptop X1",
     *   "price": 2500.50,
     *   "available": 10,
     *   "description": "High-performance business laptop"
     * }
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            // Create product using validated data from FormRequest
            $product = $this->productRepository->store($request->validated());

            // Return created product with success message and 201 Created status
            return $this->successResponse($product, 'Product created successfully.', 201);

        } catch (Exception $e) {
            // Any exception (e.g., DB or validation) is caught and formatted
            return $this->errorResponse('Failed to create product.', $e->getMessage());
        }
    }

    /**
     * Display the specified product by ID.
     *
     * @param int $id
     * @return JsonResponse
     *
     * Example: GET /api/products/7
     */
    public function show(int $id): JsonResponse
    {
        // Try to locate the product via repository
        $product = $this->productRepository->findById($id);

        if (!$product) {
            return $this->notFoundResponse('Product not found.');
        }

        return $this->successResponse($product, 'Product retrieved successfully.');
    }

    /**
     * Update the specified product in storage.
     *
     * @param UpdateProductRequest $request
     * @param Product $product
     * @return JsonResponse
     *
     * Example Payload:
     * {
     *   "name": "Updated Laptop X1",
     *   "price": 2600.00,
     *   "available": 12,
     *   "description": "Updated model with enhanced specs"
     * }
     *
     * Example: PUT /api/products/update/7
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        try {
            // Update existing product with validated data
            $this->productRepository->update($product, $request->validated());

            // Refresh ensures the response contains updated values from the DB
            return $this->successResponse($product->refresh(), 'Product updated successfully.');

        } catch (Exception $e) {
            return $this->errorResponse('Failed to update product.', $e->getMessage());
        }
    }

    /**
     * Remove the specified product from storage.
     *
     * @param Product $product
     * @return JsonResponse
     *
     * Example: DELETE /api/products/delete/7
     */
    public function destroy(Product $product): JsonResponse
    {
        try {
            // Delete the product record from the database
            $this->productRepository->delete($product);

            return $this->successResponse(null, 'Product deleted successfully.');

        } catch (Exception $e) {
            return $this->errorResponse('Failed to delete product.', $e->getMessage());
        }
    }
}
