<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateProductRequest
 *
 * Handles validation rules for updating an existing product.
 *
 * @package App\Http\Requests
 */
class UpdateProductRequest extends FormRequest
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
     * Get validation rules for updating a product.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/', 'min:1'],
            'available' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get custom validation messages for product update errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be numeric.',
            'price.regex' => 'Price format is invalid (e.g., 10.99).',
            'price.min' => 'Price must be at least 1.',
            'available.required' => 'Availability status is required.',
            'available.numeric' => 'Availability must be numeric.',
            'available.min' => 'Availability must be 0 or greater.',
        ];
    }
}
