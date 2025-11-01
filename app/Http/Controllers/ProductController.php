<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    use ApiResponseTrait;

    public function index(): JsonResponse
    {
        $products = Product::with('orderItems')->paginate(10);
        return $this->successResponse(ProductResource::collection($products), 'Products retrieved successfully.');
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());
        return $this->successResponse(new ProductResource($product), 'Product created successfully.', 201);
    }

    public function show($id): JsonResponse
    {
        $product = Product::with('orderItems')->find($id);

        if (!$product) {
            return $this->notFoundResponse('Product not found.');
        }

        return $this->successResponse(new ProductResource($product), 'Product retrieved successfully.');
    }

    public function update(UpdateProductRequest $request, $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->notFoundResponse('Product not found.');
        }

        $product->update($request->validated());

        return $this->successResponse(new ProductResource($product), 'Product updated successfully.');
    }

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
