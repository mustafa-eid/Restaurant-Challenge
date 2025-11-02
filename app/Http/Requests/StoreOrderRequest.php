<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreOrderRequest
 *
 * Handles validation rules for creating a new order.
 *
 * @package App\Http\Requests
 */
class StoreOrderRequest extends FormRequest
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
     * Get validation rules for storing a new order.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'total_amount' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Get custom validation messages for order creation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'branch_id.required' => 'Branch ID is required.',
            'branch_id.exists' => 'The selected branch does not exist.',
            'name.required' => 'Order name is required.',
            'name.string' => 'Order name must be a string.',
            'name.max' => 'Order name must not exceed 255 characters.',
            'total_amount.numeric' => 'Total amount must be a number.',
            'total_amount.min' => 'Total amount cannot be negative.',
        ];
    }
}
