<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShoppingListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->households->contains($this->household_id);
    }

    public function rules(): array
    {
        return [
            'household_id' => 'required|exists:households,id',
            'name' => 'required|string|max:255',
            'is_public' => 'boolean',
            'store' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'household_id.required' => 'Please select a household.',
            'household_id.exists' => 'The selected household does not exist.',
            'name.required' => 'Please provide a name for the shopping list.',
            'name.max' => 'The list name cannot exceed 255 characters.',
        ];
    }
}
