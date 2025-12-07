<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTOs\ProjectDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectService $projectService
    ) {
    }

    /**
     * Create a new project.
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $dto = ProjectDTO::fromArray($request->validated());
        $project = $this->projectService->create($request->user(), $dto);

        return response()->json([
            'message' => 'Project created successfully',
            'data' => new ProjectResource($project),
        ], 201);
    }

    /**
     * Get a specific project by ID.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $project = $this->projectService->findById($request->user(), $id);

        if (!$project) {
            return response()->json([
                'message' => 'Project not found',
            ], 404);
        }

        return response()->json([
            'data' => new ProjectResource($project),
        ]);
    }

    /**
     * Get all projects with optional filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['name', 'department', 'status', 'start_date', 'end_date']);
        $projects = $this->projectService->findAll($request->user(), $filters);

        return response()->json([
            'data' => ProjectResource::collection($projects),
        ]);
    }

    /**
     * Update a project.
     */
    public function update(UpdateProjectRequest $request): JsonResponse
    {
        $id = (int) $request->input('id');
        $dto = ProjectDTO::fromArray($request->validated());
        $project = $this->projectService->update($request->user(), $id, $dto);

        if (!$project) {
            return response()->json([
                'message' => 'Project not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Project updated successfully',
            'data' => new ProjectResource($project),
        ]);
    }

    /**
     * Delete a project.
     */
    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'id' => ['required', 'integer', 'exists:projects,id'],
        ]);

        $deleted = $this->projectService->delete($request->user(), (int) $request->input('id'));

        if (!$deleted) {
            return response()->json([
                'message' => 'Project not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Project deleted successfully',
        ]);
    }
}
