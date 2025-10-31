<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the order item resource into an array for JSON response.
     *
     * Includes related product & order info and calculated subtotal.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'subtotal' => number_format($this->quantity * $this->price, 2),

            // handle null timestamps safely
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,

            // Include related product details
            'product' => $this->whenLoaded('product', function () {
                return [
                    'id' => $this->product->id ?? null,
                    'name' => $this->product->name ?? null,
                    'price' => $this->product->price ?? null,
                    'available' => $this->product->available ?? null,
                ];
            }),

            // Include related order details
            'order' => $this->whenLoaded('order', function () {
                return [
                    'id' => $this->order->id ?? null,
                    'name' => $this->order->name ?? null,
                    'total_amount' => $this->order->total_amount ?? null,
                ];
            }),
        ];
    }
}
