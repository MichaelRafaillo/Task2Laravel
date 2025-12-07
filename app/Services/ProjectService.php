<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ProjectDTO;
use App\Models\Project;
use App\Models\User;
use App\Policies\ProjectPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;

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
        try {
            if (!$this->policy->create($authenticatedUser)) {
                throw new AuthorizationException('This action is unauthorized.');
            }

            return Project::create($dto->toArray());
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (QueryException $e) {
            report($e);
            throw new \RuntimeException('Failed to create project. Please try again.', 0, $e);
        } catch (\Exception $e) {
            report($e);
            throw new \RuntimeException('An unexpected error occurred. Please try again.', 0, $e);
        }
    }

    /**
     * Find a project by ID.
     */
    public function findById(User $authenticatedUser, int $id): ?Project
    {
        try {
            $project = Project::find($id);

            if ($project && !$this->policy->view($authenticatedUser, $project)) {
                throw new AuthorizationException('This action is unauthorized.');
            }

            return $project;
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (QueryException $e) {
            report($e);
            throw new \RuntimeException('Failed to retrieve project. Please try again.', 0, $e);
        } catch (\Exception $e) {
            report($e);
            throw new \RuntimeException('An unexpected error occurred. Please try again.', 0, $e);
        }
    }

    /**
     * Find all projects with optional filtering.
     *
     * @param array<string, mixed> $filters
     */
    public function findAll(User $authenticatedUser, array $filters = []): Collection
    {
        try {
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
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (QueryException $e) {
            report($e);
            throw new \RuntimeException('Failed to retrieve projects. Please try again.', 0, $e);
        } catch (\Exception $e) {
            report($e);
            throw new \RuntimeException('An unexpected error occurred. Please try again.', 0, $e);
        }
    }

    /**
     * Update a project.
     */
    public function update(User $authenticatedUser, int $id, ProjectDTO $dto): ?Project
    {
        try {
            $project = Project::find($id);

            if (!$project) {
                return null;
            }

            if (!$this->policy->update($authenticatedUser, $project)) {
                throw new AuthorizationException('This action is unauthorized.');
            }

            $project->update($dto->toArray());

            return $project->fresh();
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (QueryException $e) {
            report($e);
            throw new \RuntimeException('Failed to update project. Please try again.', 0, $e);
        } catch (\Exception $e) {
            report($e);
            throw new \RuntimeException('An unexpected error occurred. Please try again.', 0, $e);
        }
    }

    /**
     * Delete a project and related timesheets.
     */
    public function delete(User $authenticatedUser, int $id): bool
    {
        try {
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
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (QueryException $e) {
            report($e);
            throw new \RuntimeException('Failed to delete project. Please try again.', 0, $e);
        } catch (\Exception $e) {
            report($e);
            throw new \RuntimeException('An unexpected error occurred. Please try again.', 0, $e);
        }
    }
}
