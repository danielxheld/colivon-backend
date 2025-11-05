<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShoppingListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'household_id' => $this->household_id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'is_public' => (bool) $this->is_public,
            'store' => $this->store,
            'currently_shopping_by_id' => $this->currently_shopping_by_id,
            'is_template' => (bool) $this->is_template,
            'template_name' => $this->template_name,
            'estimated_total' => $this->estimated_total ? (float) $this->estimated_total : null,
            'actual_total' => $this->actual_total ? (float) $this->actual_total : null,
            'last_sync' => $this->last_sync?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),
            'currently_shopping_by' => new UserResource($this->whenLoaded('currentlyShoppingBy')),
            'items' => ShoppingListItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->when(isset($this->items_count), $this->items_count),
        ];
    }
}
