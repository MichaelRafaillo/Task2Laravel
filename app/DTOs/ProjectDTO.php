<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class ProjectDTO
{
    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
        public ?string $department = null,
        public ?string $startDate = null,
        public ?string $endDate = null,
        public ?string $status = null,
    ) {
    }

    /**
     * Create DTO from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            name: $data['name'] ?? null,
            department: $data['department'] ?? null,
            startDate: $data['start_date'] ?? $data['startDate'] ?? null,
            endDate: $data['end_date'] ?? $data['endDate'] ?? null,
            status: $data['status'] ?? null,
        );
    }

    /**
     * Convert DTO to array for database operations.
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->name !== null) {
            $data['name'] = $this->name;
        }
        if ($this->department !== null) {
            $data['department'] = $this->department;
        }
        if ($this->startDate !== null) {
            $data['start_date'] = $this->startDate;
        }
        if ($this->endDate !== null) {
            $data['end_date'] = $this->endDate;
        }
        if ($this->status !== null) {
            $data['status'] = $this->status;
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
            name: $other->name ?? $this->name,
            department: $other->department ?? $this->department,
            startDate: $other->startDate ?? $this->startDate,
            endDate: $other->endDate ?? $this->endDate,
            status: $other->status ?? $this->status,
        );
    }
}

