<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id'    => 'required|exists:branches,id',
            'name'         => 'required|string|max:255',
            'total_amount' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'branch_id.required' => 'Branch ID is required.',
            'branch_id.exists'   => 'The selected branch does not exist.',
            'name.required'      => 'Order name is required.',
            'name.string'        => 'Order name must be a string.',
            'name.max'           => 'Order name must not exceed 255 characters.',
            'total_amount.numeric' => 'Total amount must be a valid number.',
            'total_amount.min'     => 'Total amount cannot be negative.',
        ];
    }
}
