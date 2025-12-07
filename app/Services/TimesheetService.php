<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\TimesheetDTO;
use App\Models\Timesheet;
use App\Models\User;
use App\Policies\TimesheetPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;

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
        try {
            if (!$this->policy->create($authenticatedUser)) {
                throw new AuthorizationException('This action is unauthorized.');
            }

            return Timesheet::create($dto->toArray());
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (QueryException $e) {
            report($e);
            throw new \RuntimeException('Failed to create timesheet. Please try again.', 0, $e);
        } catch (\Exception $e) {
            report($e);
            throw new \RuntimeException('An unexpected error occurred. Please try again.', 0, $e);
        }
    }

    /**
     * Find a timesheet by ID.
     */
    public function findById(User $authenticatedUser, int $id): ?Timesheet
    {
        try {
            $timesheet = Timesheet::with(['user', 'project'])->find($id);

            if ($timesheet && !$this->policy->view($authenticatedUser, $timesheet)) {
                throw new AuthorizationException('This action is unauthorized.');
            }

            return $timesheet;
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (QueryException $e) {
            report($e);
            throw new \RuntimeException('Failed to retrieve timesheet. Please try again.', 0, $e);
        } catch (\Exception $e) {
            report($e);
            throw new \RuntimeException('An unexpected error occurred. Please try again.', 0, $e);
        }
    }

    /**
     * Find all timesheets with optional filtering.
     *
     * @param array<string, mixed> $filters
     */
    public function findAll(User $authenticatedUser, array $filters = []): Collection
    {
        try {
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
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (QueryException $e) {
            report($e);
            throw new \RuntimeException('Failed to retrieve timesheets. Please try again.', 0, $e);
        } catch (\Exception $e) {
            report($e);
            throw new \RuntimeException('An unexpected error occurred. Please try again.', 0, $e);
        }
    }

    /**
     * Update a timesheet.
     */
    public function update(User $authenticatedUser, int $id, TimesheetDTO $dto): ?Timesheet
    {
        try {
            $timesheet = Timesheet::find($id);

            if (!$timesheet) {
                return null;
            }

            if (!$this->policy->update($authenticatedUser, $timesheet)) {
                throw new AuthorizationException('This action is unauthorized.');
            }

            $timesheet->update($dto->toArray());

            return $timesheet->fresh()->load(['user', 'project']);
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (QueryException $e) {
            report($e);
            throw new \RuntimeException('Failed to update timesheet. Please try again.', 0, $e);
        } catch (\Exception $e) {
            report($e);
            throw new \RuntimeException('An unexpected error occurred. Please try again.', 0, $e);
        }
    }

    /**
     * Delete a timesheet.
     */
    public function delete(User $authenticatedUser, int $id): bool
    {
        try {
            $timesheet = Timesheet::find($id);

            if (!$timesheet) {
                return false;
            }

            if (!$this->policy->delete($authenticatedUser, $timesheet)) {
                throw new AuthorizationException('This action is unauthorized.');
            }

            return $timesheet->delete();
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (QueryException $e) {
            report($e);
            throw new \RuntimeException('Failed to delete timesheet. Please try again.', 0, $e);
        } catch (\Exception $e) {
            report($e);
            throw new \RuntimeException('An unexpected error occurred. Please try again.', 0, $e);
        }
    }
}
