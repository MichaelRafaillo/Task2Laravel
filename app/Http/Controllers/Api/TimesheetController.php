<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTOs\TimesheetDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTimesheetRequest;
use App\Http\Requests\UpdateTimesheetRequest;
use App\Http\Resources\TimesheetResource;
use App\Services\TimesheetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimesheetController extends Controller
{
    public function __construct(
        private readonly TimesheetService $timesheetService
    ) {
    }

    /**
     * Create a new timesheet.
     */
    public function store(StoreTimesheetRequest $request): JsonResponse
    {
        $dto = TimesheetDTO::fromArray($request->validated());
        $timesheet = $this->timesheetService->create($dto);

        return response()->json([
            'message' => 'Timesheet created successfully',
            'data' => new TimesheetResource($timesheet->load(['user', 'project'])),
        ], 201);
    }

    /**
     * Get a specific timesheet by ID.
     */
    public function show(int $id): JsonResponse
    {
        $timesheet = $this->timesheetService->findById($id);

        if (!$timesheet) {
            return response()->json([
                'message' => 'Timesheet not found',
            ], 404);
        }

        return response()->json([
            'data' => new TimesheetResource($timesheet),
        ]);
    }

    /**
     * Get all timesheets with optional filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['user_id', 'project_id', 'task_name', 'date', 'hours']);
        $timesheets = $this->timesheetService->findAll($filters);

        return response()->json([
            'data' => TimesheetResource::collection($timesheets),
        ]);
    }

    /**
     * Update a timesheet.
     */
    public function update(UpdateTimesheetRequest $request): JsonResponse
    {
        $id = (int) $request->input('id');
        $dto = TimesheetDTO::fromArray($request->validated());
        $timesheet = $this->timesheetService->update($id, $dto);

        if (!$timesheet) {
            return response()->json([
                'message' => 'Timesheet not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Timesheet updated successfully',
            'data' => new TimesheetResource($timesheet),
        ]);
    }

    /**
     * Delete a timesheet.
     */
    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'id' => ['required', 'integer', 'exists:timesheets,id'],
        ]);

        $deleted = $this->timesheetService->delete((int) $request->input('id'));

        if (!$deleted) {
            return response()->json([
                'message' => 'Timesheet not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Timesheet deleted successfully',
        ]);
    }
}
