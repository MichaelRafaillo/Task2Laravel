<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTOs\UserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {
    }

    /**
     * Create a new user.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $dto = UserDTO::fromArray($request->validated());
        $user = $this->userService->create($request->user(), $dto);

        return response()->json([
            'message' => 'User created successfully',
            'data' => new UserResource($user),
        ], 201);
    }

    /**
     * Get a specific user by ID.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $this->userService->findById($request->user(), $id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Get all users with optional filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['first_name', 'last_name', 'gender', 'date_of_birth', 'email']);
        $users = $this->userService->findAll($request->user(), $filters);

        return response()->json([
            'data' => UserResource::collection($users),
        ]);
    }

    /**
     * Update a user.
     */
    public function update(UpdateUserRequest $request): JsonResponse
    {
        $id = (int) $request->input('id');
        $dto = UserDTO::fromArray($request->validated());
        $user = $this->userService->update($request->user(), $id, $dto);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Delete a user.
     */
    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $deleted = $this->userService->delete($request->user(), (int) $request->input('id'));

        if (!$deleted) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }
}
