<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GamificationStatResource extends JsonResource
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
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'household_id' => $this->household_id,
            'total_xp' => $this->total_xp,
            'level' => $this->level,
            'current_streak' => $this->current_streak,
            'longest_streak' => $this->longest_streak,
            'total_chores_completed' => $this->total_chores_completed,
            'current_month_xp' => $this->current_month_xp,
            'current_month_chores' => $this->current_month_chores,
            'title' => $this->title,
            'xp_for_next_level' => $this->xpForNextLevel(),
            'level_progress' => round($this->levelProgress(), 1),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
