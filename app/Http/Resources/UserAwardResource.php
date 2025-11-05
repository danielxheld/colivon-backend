<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAwardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'household_id' => $this->household_id,
            'award' => new AwardResource($this->whenLoaded('award')),
            'earned_at' => $this->earned_at,
            'progress' => $this->progress,
            'created_at' => $this->created_at,
        ];
    }
}
