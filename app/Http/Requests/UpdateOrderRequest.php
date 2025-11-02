<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateOrderRequest
 *
 * Handles validation rules for updating an existing order.
 *
 * @package App\Http\Requests
 */
class UpdateOrderRequest extends FormRequest
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
     * Get validation rules for updating an order.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    /**
     * Get custom validation messages for order update errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Order name is required.',
            'name.string' => 'Order name must be a string.',
            'name.max' => 'Order name must not exceed 255 characters.',
        ];
    }
}
