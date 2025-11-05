<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChoreResource extends JsonResource
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
            'household_id' => $this->household_id,
            'created_by' => $this->created_by,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'title' => $this->title,
            'description' => $this->description,
            'recurrence_type' => $this->recurrence_type,
            'recurrence_interval' => $this->recurrence_interval,
            'difficulty_points' => $this->difficulty_points,
            'estimated_duration' => $this->estimated_duration,
            'requires_photo' => $this->requires_photo,
            'is_active' => $this->is_active,
            'assignment_mode' => $this->assignment_mode,
            'current_assignment' => new ChoreAssignmentResource($this->whenLoaded('currentAssignment')),
            'assignments' => ChoreAssignmentResource::collection($this->whenLoaded('assignments')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
