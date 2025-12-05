<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\UserDTO;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Create a new user.
     */
    public function create(UserDTO $dto): User
    {
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
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Find all users with optional filtering.
     *
     * @param array<string, mixed> $filters
     */
    public function findAll(array $filters = []): Collection
    {
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
    public function update(int $id, UserDTO $dto): ?User
    {
        $user = $this->findById($id);

        if (!$user) {
            return null;
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
    public function delete(int $id): bool
    {
        $user = $this->findById($id);

        if (!$user) {
            return false;
        }

        // Delete related timesheets (cascade delete handles this, but we can be explicit)
        $user->timesheets()->delete();

        return $user->delete();
    }
}

