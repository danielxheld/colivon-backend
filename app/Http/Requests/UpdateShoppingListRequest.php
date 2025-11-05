<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShoppingListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('shoppingList')->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'is_public' => 'boolean',
            'store' => 'nullable|string|max:100',
            'is_template' => 'boolean',
            'template_name' => 'nullable|string|max:255',
            'estimated_total' => 'nullable|numeric|min:0',
            'actual_total' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please provide a name for the shopping list.',
            'estimated_total.numeric' => 'The estimated total must be a valid number.',
            'actual_total.numeric' => 'The actual total must be a valid number.',
        ];
    }
}
