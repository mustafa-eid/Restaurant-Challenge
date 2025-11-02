<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ProductResource
 *
 * Transforms a Product model into a structured array for API responses.
 * Includes related order items if they are eager-loaded.
 *
 * @package App\Http\Resources
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array for JSON output.
     *
     * @param  \Illuminate\Http\Request  $request  The incoming HTTP request.
     * @return array<string, mixed>  The formatted product data.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'available' => $this->available,
            'order_items' => $this->whenLoaded('orderItems', function () {
                return $this->formatOrderItems();
            }),
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }

    /**
     * Format the related order items data when the relationship is loaded.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function formatOrderItems()
    {
        return $this->orderItems->map(function ($item) {
            return [
                'id' => $item->id,
                'order_id' => $item->order_id,
                'quantity' => $item->quantity,
                'subtotal' => $item->quantity * $this->price,
            ];
        });
    }
}
