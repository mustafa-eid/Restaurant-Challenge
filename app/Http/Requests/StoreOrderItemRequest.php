<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id'   => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'price'      => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required'   => 'Order ID is required.',
            'order_id.exists'     => 'The selected order does not exist.',
            'product_id.required' => 'Product ID is required.',
            'product_id.exists'   => 'The selected product does not exist.',
            'quantity.required'   => 'Quantity is required.',
            'quantity.integer'    => 'Quantity must be an integer.',
            'quantity.min'        => 'Quantity must be at least 1.',
            'price.required'      => 'Price is required.',
            'price.numeric'       => 'Price must be a valid number.',
            'price.min'           => 'Price must be at least 0.',
        ];
    }
}
