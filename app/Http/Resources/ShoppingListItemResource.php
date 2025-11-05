<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShoppingListItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shopping_list_id' => $this->shopping_list_id,
            'name' => $this->name,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'category' => $this->category,
            'note' => $this->note,
            'price' => $this->price ? (float) $this->price : null,
            'aisle_order' => $this->aisle_order,
            'image_url' => $this->image_url,
            'is_completed' => (bool) $this->is_completed,
            'is_recurring' => (bool) $this->is_recurring,
            'recurrence_interval' => $this->recurrence_interval,
            'completed_at' => $this->completed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
