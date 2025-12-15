<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                // Name must be unique for this user
                Rule::unique('categories')->where('user_id', auth()->id())
            ],
            'color' => [
                'required',
                'string',
                'regex:/^#[0-9A-F]{6}$/i'  // Must be hex color like #FF0000
            ],
        ];
    }
    
    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required',
            'name.unique' => 'You already have a category with this name',
            'color.required' => 'Please select a color',
            'color.regex' => 'Color must be in hex format (#FF0000)',
        ];
    }
}
