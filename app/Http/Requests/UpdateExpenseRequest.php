<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    // Check if user is authorized to make this request
    public function authorize(): bool
    {
        return true; // Allow all authenticated users
    }

    // Validation rules
    public function rules(): array
    {
        return [
            'amount' => [
                'required',        // Must be provided
                'numeric',         // Must be a number
                'min:0.01',        // Minimum $0.01
                'max:1000000'      // Maximum $1,000,000
            ],
            'category_id' => [
                'required',
                // Category must exist AND belong to current user
                Rule::exists('categories', 'id')->where('user_id', auth()->id())
            ],
            'date' => [
                'required',
                'date',                   // Must be valid date
                'before_or_equal:today'   // Can't be future date
            ],
            'description' => [
                'nullable',      // Optional field
                'string',        // Must be text
                'max:500'        // Maximum 500 characters
            ],
        ];
    }

    // Custom error messages
    public function messages(): array
    {
        return [
            'amount.required' => 'Please enter the amount',
            'amount.min' => 'Amount must be at least $0.01',
            'category_id.required' => 'Please select a category',
            'category_id.exists' => 'Selected category does not exist',
            'date.required' => 'Date is required',
            'date.before_or_equal' => 'Date cannot be in the future',
        ];
    }
}