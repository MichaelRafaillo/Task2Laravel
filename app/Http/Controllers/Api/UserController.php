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
        $user = $this->userService->create($dto);

        return response()->json([
            'message' => 'User created successfully',
            'data' => new UserResource($user),
        ], 201);
    }

    /**
     * Get a specific user by ID.
     */
    public function show(int $id): JsonResponse
    {
        $user = $this->userService->findById($id);

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
        $users = $this->userService->findAll($filters);

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
        $user = $this->userService->update($id, $dto);

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

        $deleted = $this->userService->delete((int) $request->input('id'));

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
