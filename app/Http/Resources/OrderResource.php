<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class OrderResource
 *
 * Transforms an Order model into a structured array for API responses.
 * Includes order items if they are eager-loaded.
 *
 * @package App\Http\Resources
 */
class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array for JSON output.
     *
     * @param  \Illuminate\Http\Request  $request  The incoming HTTP request.
     * @return array<string, mixed>  The formatted order data.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'total_amount' => $this->total_amount,
            'items' => $this->whenLoaded('items', function () {
                return $this->formatOrderItems();
            }),
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }

    /**
     * Format the order items data when the relationship is loaded.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function formatOrderItems()
    {
        return $this->items->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'subtotal' => $item->quantity * $item->price,
            ];
        });
    }
}
