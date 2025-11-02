<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class OrderItemResource
 *
 * Transforms an OrderItem model into a structured array for API responses.
 * This resource includes related order and product details when loaded.
 *
 * @package App\Http\Resources
 */
class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array for JSON output.
     *
     * @param  \Illuminate\Http\Request  $request  The incoming HTTP request.
     * @return array<string, mixed>  The formatted order item data.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'subtotal' => $this->quantity && $this->price
                ? number_format($this->quantity * $this->price, 2)
                : null,

            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,

            // Include product data when the relationship is loaded
            'product' => $this->whenLoaded('product', function () {
                return $this->formatProductData();
            }),

            // Include order data when the relationship is loaded
            'order' => $this->whenLoaded('order', function () {
                return $this->formatOrderData();
            }),
        ];
    }

    /**
     * Format related product data.
     *
     * @return array<string, mixed>  The product data.
     */
    private function formatProductData(): array
    {
        return [
            'id' => $this->product->id ?? null,
            'name' => $this->product->name ?? null,
            'price' => $this->product->price ?? null,
            'available' => $this->product->available ?? null,
        ];
    }

    /**
     * Format related order data.
     *
     * @return array<string, mixed>  The order data.
     */
    private function formatOrderData(): array
    {
        return [
            'id' => $this->order->id ?? null,
            'name' => $this->order->name ?? null,
            'total_amount' => $this->order->total_amount ?? null,
        ];
    }
}
