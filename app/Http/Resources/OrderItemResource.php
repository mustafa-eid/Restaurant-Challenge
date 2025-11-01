<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
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

            'product' => $this->whenLoaded('product', function () {
                return [
                    'id' => $this->product->id ?? null,
                    'name' => $this->product->name ?? null,
                    'price' => $this->product->price ?? null,
                    'available' => $this->product->available ?? null,
                ];
            }),

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
