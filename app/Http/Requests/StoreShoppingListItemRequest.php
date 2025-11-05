<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShoppingListItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $shoppingList = $this->route('shoppingList');
        return $shoppingList->user_id === $this->user()->id
            || ($shoppingList->is_public && $this->user()->households->contains($shoppingList->household_id));
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'quantity' => 'nullable|string|max:50',
            'unit' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:100',
            'note' => 'nullable|string|max:500',
            'price' => 'nullable|numeric|min:0|max:99999.99',
            'aisle_order' => 'nullable|integer|min:0',
            'is_recurring' => 'boolean',
            'recurrence_interval' => 'nullable|in:daily,weekly,monthly',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please provide an item name.',
            'price.numeric' => 'Price must be a valid number.',
            'price.max' => 'Price cannot exceed 99,999.99.',
            'recurrence_interval.in' => 'Recurrence interval must be daily, weekly, or monthly.',
        ];
    }
}
