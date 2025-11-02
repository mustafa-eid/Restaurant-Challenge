<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateOrderItemRequest
 *
 * Handles validation rules for updating an order item.
 *
 * @package App\Http\Requests
 */
class UpdateOrderItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get validation rules for updating an order item.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'order_id' => 'sometimes|exists:orders,id',
            'product_id' => 'sometimes|exists:products,id',
            'quantity' => 'sometimes|integer|min:1',
            'price' => 'sometimes|numeric|min:0',
        ];
    }

    /**
     * Get custom validation messages for order item update errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'order_id.exists' => 'The selected order does not exist.',
            'product_id.exists' => 'The selected product does not exist.',
            'quantity.integer' => 'Quantity must be an integer.',
            'quantity.min' => 'Quantity must be at least 1.',
            'price.numeric' => 'Price must be numeric.',
            'price.min' => 'Price must be at least 0.',
        ];
    }
}
