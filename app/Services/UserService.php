<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\UserDTO;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        private readonly UserPolicy $policy
    ) {
    }

    /**
     * Create a new user.
     */
    public function create(User $authenticatedUser, UserDTO $dto): User
    {
        if (!$this->policy->create($authenticatedUser)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        $data = $dto->toArray();
        
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return User::create($data);
    }

    /**
     * Find a user by ID.
     */
    public function findById(User $authenticatedUser, int $id): ?User
    {
        $user = User::find($id);

        if ($user && !$this->policy->view($authenticatedUser, $user)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        return $user;
    }

    /**
     * Find all users with optional filtering.
     *
     * @param array<string, mixed> $filters
     */
    public function findAll(User $authenticatedUser, array $filters = []): Collection
    {
        if (!$this->policy->viewAny($authenticatedUser)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        $query = User::query();

        // Apply filters with AND operation
        if (isset($filters['first_name'])) {
            $query->where('first_name', 'like', '%' . $filters['first_name'] . '%');
        }

        if (isset($filters['last_name'])) {
            $query->where('last_name', 'like', '%' . $filters['last_name'] . '%');
        }

        if (isset($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (isset($filters['date_of_birth'])) {
            $query->whereDate('date_of_birth', $filters['date_of_birth']);
        }

        if (isset($filters['email'])) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        return $query->get();
    }

    /**
     * Update a user.
     */
    public function update(User $authenticatedUser, int $id, UserDTO $dto): ?User
    {
        $user = User::find($id);

        if (!$user) {
            return null;
        }

        if (!$this->policy->update($authenticatedUser, $user)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        $data = $dto->toArray();

        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return $user->fresh();
    }

    /**
     * Delete a user and related timesheets.
     */
    public function delete(User $authenticatedUser, int $id): bool
    {
        $user = User::find($id);

        if (!$user) {
            return false;
        }

        if (!$this->policy->delete($authenticatedUser, $user)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        // Delete related timesheets (cascade delete handles this, but we can be explicit)
        $user->timesheets()->delete();

        return $user->delete();
    }
}
