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
use Illuminate\Http\Response;

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
        $timesheet = $this->timesheetService->create($request->user(), $dto);

        return response()->json([
            'message' => 'Timesheet created successfully',
            'data' => new TimesheetResource($timesheet->load(['user', 'project'])),
        ], Response::HTTP_CREATED);
    }

    /**
     * Get a specific timesheet by ID.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $timesheet = $this->timesheetService->findById($request->user(), $id);

        if (!$timesheet) {
            return response()->json([
                'message' => 'Timesheet not found',
            ], Response::HTTP_NOT_FOUND);
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
        $timesheets = $this->timesheetService->findAll($request->user(), $filters);

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
        $timesheet = $this->timesheetService->update($request->user(), $id, $dto);

        if (!$timesheet) {
            return response()->json([
                'message' => 'Timesheet not found',
            ], Response::HTTP_NOT_FOUND);
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

        $deleted = $this->timesheetService->delete($request->user(), (int) $request->input('id'));

        if (!$deleted) {
            return response()->json([
                'message' => 'Timesheet not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Timesheet deleted successfully',
        ]);
    }
}
