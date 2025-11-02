<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

/**
 * Class ProductController
 *
 * Handles CRUD operations for products.
 * Provides endpoints for managing available products in the system.
 *
 * @package App\Http\Controllers
 */
class ProductController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a paginated list of products with their related order items.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $products = Product::with('orderItems')->paginate(10);
        return $this->successResponse(ProductResource::collection($products), 'Products retrieved successfully.');
    }

    /**
     * Store a new product.
     *
     * @param  StoreProductRequest  $request
     * @return JsonResponse
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());
        return $this->successResponse(new ProductResource($product), 'Product created successfully.', 201);
    }

    /**
     * Display a specific product by ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $product = Product::with('orderItems')->find($id);

        if (!$product) {
            return $this->notFoundResponse('Product not found.');
        }

        return $this->successResponse(new ProductResource($product), 'Product retrieved successfully.');
    }

    /**
     * Update a product.
     *
     * @param  UpdateProductRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(UpdateProductRequest $request, $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->notFoundResponse('Product not found.');
        }

        $product->update($request->validated());

        return $this->successResponse(new ProductResource($product), 'Product updated successfully.');
    }

    /**
     * Delete a product by ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->notFoundResponse('Product not found.');
        }

        $product->delete();

        return $this->successResponse(null, 'Product deleted successfully.');
    }
}
