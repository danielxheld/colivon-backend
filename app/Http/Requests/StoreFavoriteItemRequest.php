<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFavoriteItemRequest extends FormRequest
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
            'category' => 'nullable|string|max:100',
            'quantity' => 'nullable|string|max:50',
            'unit' => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'household_id.required' => 'Please select a household.',
            'name.required' => 'Please provide an item name.',
        ];
    }
}
