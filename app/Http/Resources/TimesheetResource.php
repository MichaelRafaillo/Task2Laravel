<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimesheetResource extends JsonResource
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
            'project_id' => $this->project_id,
            'task_name' => $this->task_name,
            'date' => $this->date?->format('Y-m-d'),
            'hours' => (float) $this->hours,
            'user' => $this->whenLoaded('user', fn () => new UserResource($this->user)),
            'project' => $this->whenLoaded('project', fn () => new ProjectResource($this->project)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
