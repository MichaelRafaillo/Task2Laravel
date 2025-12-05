<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ProjectDTO;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

class ProjectService
{
    /**
     * Create a new project.
     */
    public function create(ProjectDTO $dto): Project
    {
        return Project::create($dto->toArray());
    }

    /**
     * Find a project by ID.
     */
    public function findById(int $id): ?Project
    {
        return Project::find($id);
    }

    /**
     * Find all projects with optional filtering.
     *
     * @param array<string, mixed> $filters
     */
    public function findAll(array $filters = []): Collection
    {
        $query = Project::query();

        // Apply filters with AND operation
        if (isset($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (isset($filters['department'])) {
            $query->where('department', 'like', '%' . $filters['department'] . '%');
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['start_date'])) {
            $query->whereDate('start_date', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('end_date', $filters['end_date']);
        }

        return $query->get();
    }

    /**
     * Update a project.
     */
    public function update(int $id, ProjectDTO $dto): ?Project
    {
        $project = $this->findById($id);

        if (!$project) {
            return null;
        }

        $project->update($dto->toArray());

        return $project->fresh();
    }

    /**
     * Delete a project and related timesheets.
     */
    public function delete(int $id): bool
    {
        $project = $this->findById($id);

        if (!$project) {
            return false;
        }

        // Delete related timesheets (cascade delete handles this, but we can be explicit)
        $project->timesheets()->delete();

        return $project->delete();
    }
}

