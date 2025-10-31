<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id'   => 'sometimes|exists:orders,id',
            'product_id' => 'sometimes|exists:products,id',
            'quantity'   => 'sometimes|integer|min:1',
            'price'      => 'sometimes|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.exists'     => 'The selected order does not exist.',
            'product_id.exists'   => 'The selected product does not exist.',
            'quantity.integer'    => 'Quantity must be an integer.',
            'quantity.min'        => 'Quantity must be at least 1.',
            'price.numeric'       => 'Price must be a valid number.',
            'price.min'           => 'Price must be at least 0.',
        ];
    }
}
