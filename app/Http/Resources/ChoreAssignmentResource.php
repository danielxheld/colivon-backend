<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChoreAssignmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'chore_id' => $this->chore_id,
            'chore' => new ChoreResource($this->whenLoaded('chore')),
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'assigned_at' => $this->assigned_at?->toISOString(),
            'due_date' => $this->due_date?->toDateString(),
            'status' => $this->status,
            'assigned_by' => $this->assigned_by,
            'is_overdue' => $this->isOverdue(),
            'completion' => new ChoreCompletionResource($this->whenLoaded('completion')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
