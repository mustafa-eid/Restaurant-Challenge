<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the order resource into an array for JSON response.
     *
     * This method allows you to control exactly what data is returned
     * when an Order is serialized into JSON. It also formats related
     * items and calculates subtotals for each item.
     *
     * @param Request $request The incoming HTTP request (not used here but available)
     * @return array<string, mixed> The structured order data
     */
    public function toArray(Request $request): array
    {
        return [
            // Basic order information
            'id' => $this->id,                        // Order ID
            'name' => $this->name,                    // Customer or order name
            'total_amount' => $this->total_amount,    // Total price of the order

            // Detailed list of items in the order
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,                  // Unique ID of the order item
                    'product_id' => $item->product_id,  // Reference to the product
                    'quantity' => $item->quantity,      // Quantity of this product
                    'price' => $item->price,            // Price per unit
                    'subtotal' => $item->quantity * $item->price, // Total for this item
                ];
            }),

            // Timestamps formatted as readable strings
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
