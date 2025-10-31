<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the product resource into an array for JSON response.
     *
     * This method ensures that the API response includes all relevant
     * product details and related order items (if needed), similar
     * to how orders include items.
     *
     * @param Request $request The incoming HTTP request
     * @return array<string, mixed> The structured product data
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,                  // Product ID
            'name' => $this->name,              // Product name
            'price' => $this->price,            // Product price
            'available' => $this->available,    // Stock availability (true/false)
            'order_items' => $this->orderItems->map(function ($item) {
                return [
                    'id' => $item->id,                  // Order item ID
                    'order_id' => $item->order_id,      // Parent order ID
                    'quantity' => $item->quantity,      // Quantity in this order
                    'subtotal' => $item->quantity * $this->price, // Total price for this item
                ];
            }),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
