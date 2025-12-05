<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class TimesheetDTO
{
    public function __construct(
        public ?int $id = null,
        public ?int $userId = null,
        public ?int $projectId = null,
        public ?string $taskName = null,
        public ?string $date = null,
        public ?float $hours = null,
    ) {
    }

    /**
     * Create DTO from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            userId: $data['user_id'] ?? $data['userId'] ?? null,
            projectId: $data['project_id'] ?? $data['projectId'] ?? null,
            taskName: $data['task_name'] ?? $data['taskName'] ?? null,
            date: $data['date'] ?? null,
            hours: isset($data['hours']) ? (float) $data['hours'] : null,
        );
    }

    /**
     * Convert DTO to array for database operations.
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->userId !== null) {
            $data['user_id'] = $this->userId;
        }
        if ($this->projectId !== null) {
            $data['project_id'] = $this->projectId;
        }
        if ($this->taskName !== null) {
            $data['task_name'] = $this->taskName;
        }
        if ($this->date !== null) {
            $data['date'] = $this->date;
        }
        if ($this->hours !== null) {
            $data['hours'] = $this->hours;
        }

        return $data;
    }

    /**
     * Merge with another DTO for updates.
     */
    public function merge(self $other): self
    {
        return new self(
            id: $this->id ?? $other->id,
            userId: $other->userId ?? $this->userId,
            projectId: $other->projectId ?? $this->projectId,
            taskName: $other->taskName ?? $this->taskName,
            date: $other->date ?? $this->date,
            hours: $other->hours ?? $this->hours,
        );
    }
}

