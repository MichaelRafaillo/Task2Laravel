<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ProjectDTO;
use App\Models\Project;
use App\Models\User;
use App\Policies\ProjectPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;

class ProjectService
{
    public function __construct(
        private readonly ProjectPolicy $policy
    ) {
    }

    /**
     * Create a new project.
     */
    public function create(User $authenticatedUser, ProjectDTO $dto): Project
    {
        if (!$this->policy->create($authenticatedUser)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        return Project::create($dto->toArray());
    }

    /**
     * Find a project by ID.
     */
    public function findById(User $authenticatedUser, int $id): ?Project
    {
        $project = Project::find($id);

        if ($project && !$this->policy->view($authenticatedUser, $project)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        return $project;
    }

    /**
     * Find all projects with optional filtering.
     *
     * @param array<string, mixed> $filters
     */
    public function findAll(User $authenticatedUser, array $filters = []): Collection
    {
        if (!$this->policy->viewAny($authenticatedUser)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

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
    public function update(User $authenticatedUser, int $id, ProjectDTO $dto): ?Project
    {
        $project = Project::find($id);

        if (!$project) {
            return null;
        }

        if (!$this->policy->update($authenticatedUser, $project)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        $project->update($dto->toArray());

        return $project->fresh();
    }

    /**
     * Delete a project and related timesheets.
     */
    public function delete(User $authenticatedUser, int $id): bool
    {
        $project = Project::find($id);

        if (!$project) {
            return false;
        }

        if (!$this->policy->delete($authenticatedUser, $project)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        // Delete related timesheets (cascade delete handles this, but we can be explicit)
        $project->timesheets()->delete();

        return $project->delete();
    }
}
