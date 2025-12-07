<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\TimesheetDTO;
use App\Models\Timesheet;
use App\Models\User;
use App\Policies\TimesheetPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;

class TimesheetService
{
    public function __construct(
        private readonly TimesheetPolicy $policy
    ) {
    }

    /**
     * Create a new timesheet.
     */
    public function create(User $authenticatedUser, TimesheetDTO $dto): Timesheet
    {
        if (!$this->policy->create($authenticatedUser)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        return Timesheet::create($dto->toArray());
    }

    /**
     * Find a timesheet by ID.
     */
    public function findById(User $authenticatedUser, int $id): ?Timesheet
    {
        $timesheet = Timesheet::with(['user', 'project'])->find($id);

        if ($timesheet && !$this->policy->view($authenticatedUser, $timesheet)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        return $timesheet;
    }

    /**
     * Find all timesheets with optional filtering.
     *
     * @param array<string, mixed> $filters
     */
    public function findAll(User $authenticatedUser, array $filters = []): Collection
    {
        if (!$this->policy->viewAny($authenticatedUser)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        $query = Timesheet::with(['user', 'project']);

        // Apply filters with AND operation
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (isset($filters['task_name'])) {
            $query->where('task_name', 'like', '%' . $filters['task_name'] . '%');
        }

        if (isset($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }

        if (isset($filters['hours'])) {
            $query->where('hours', $filters['hours']);
        }

        return $query->get();
    }

    /**
     * Update a timesheet.
     */
    public function update(User $authenticatedUser, int $id, TimesheetDTO $dto): ?Timesheet
    {
        $timesheet = Timesheet::find($id);

        if (!$timesheet) {
            return null;
        }

        if (!$this->policy->update($authenticatedUser, $timesheet)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        $timesheet->update($dto->toArray());

        return $timesheet->fresh()->load(['user', 'project']);
    }

    /**
     * Delete a timesheet.
     */
    public function delete(User $authenticatedUser, int $id): bool
    {
        $timesheet = Timesheet::find($id);

        if (!$timesheet) {
            return false;
        }

        if (!$this->policy->delete($authenticatedUser, $timesheet)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        return $timesheet->delete();
    }
}
