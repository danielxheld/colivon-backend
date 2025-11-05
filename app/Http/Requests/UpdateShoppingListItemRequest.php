<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShoppingListItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route('item');
        return $item->shoppingList->user_id === $this->user()->id
            || ($item->shoppingList->is_public && $this->user()->households->contains($item->shoppingList->household_id));
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'quantity' => 'nullable|string|max:50',
            'unit' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:100',
            'note' => 'nullable|string|max:500',
            'price' => 'nullable|numeric|min:0|max:99999.99',
            'aisle_order' => 'nullable|integer|min:0',
            'is_recurring' => 'boolean',
            'recurrence_interval' => 'nullable|in:daily,weekly,monthly',
            'is_completed' => 'boolean',
        ];
    }
}
