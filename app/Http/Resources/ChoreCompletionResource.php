<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChoreCompletionResource extends JsonResource
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
            'chore_assignment_id' => $this->chore_assignment_id,
            'completed_by' => $this->completed_by,
            'completedBy' => new UserResource($this->whenLoaded('completedBy')),
            'completed_at' => $this->completed_at?->toISOString(),
            'photo_path' => $this->photo_path,
            'photo_url' => $this->photo_path ? url('storage/' . $this->photo_path) : null,
            'notes' => $this->notes,
            'xp_earned' => $this->xp_earned,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
