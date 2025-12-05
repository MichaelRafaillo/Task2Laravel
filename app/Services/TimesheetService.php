<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\TimesheetDTO;
use App\Models\Timesheet;
use Illuminate\Database\Eloquent\Collection;

class TimesheetService
{
    /**
     * Create a new timesheet.
     */
    public function create(TimesheetDTO $dto): Timesheet
    {
        return Timesheet::create($dto->toArray());
    }

    /**
     * Find a timesheet by ID.
     */
    public function findById(int $id): ?Timesheet
    {
        return Timesheet::with(['user', 'project'])->find($id);
    }

    /**
     * Find all timesheets with optional filtering.
     *
     * @param array<string, mixed> $filters
     */
    public function findAll(array $filters = []): Collection
    {
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
    public function update(int $id, TimesheetDTO $dto): ?Timesheet
    {
        $timesheet = Timesheet::find($id);

        if (!$timesheet) {
            return null;
        }

        $timesheet->update($dto->toArray());

        return $timesheet->fresh()->load(['user', 'project']);
    }

    /**
     * Delete a timesheet.
     */
    public function delete(int $id): bool
    {
        $timesheet = Timesheet::find($id);

        if (!$timesheet) {
            return false;
        }

        return $timesheet->delete();
    }
}

