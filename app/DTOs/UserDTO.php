<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class UserDTO
{
    public function __construct(
        public ?int $id = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $dateOfBirth = null,
        public ?string $gender = null,
        public ?string $email = null,
        public ?string $password = null,
    ) {
    }

    /**
     * Create DTO from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            firstName: $data['first_name'] ?? $data['firstName'] ?? null,
            lastName: $data['last_name'] ?? $data['lastName'] ?? null,
            dateOfBirth: $data['date_of_birth'] ?? $data['dateOfBirth'] ?? null,
            gender: $data['gender'] ?? null,
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
        );
    }

    /**
     * Convert DTO to array for database operations.
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->firstName !== null) {
            $data['first_name'] = $this->firstName;
        }
        if ($this->lastName !== null) {
            $data['last_name'] = $this->lastName;
        }
        if ($this->dateOfBirth !== null) {
            $data['date_of_birth'] = $this->dateOfBirth;
        }
        if ($this->gender !== null) {
            $data['gender'] = $this->gender;
        }
        if ($this->email !== null) {
            $data['email'] = $this->email;
        }
        if ($this->password !== null) {
            $data['password'] = $this->password;
        }

        return $data;
    }

    /**
     * Merge with another DTO for updates.
     */
    public function merge(self $other): self
    {
        return new self(
            id: $this->id ?? $other->id,
            firstName: $other->firstName ?? $this->firstName,
            lastName: $other->lastName ?? $this->lastName,
            dateOfBirth: $other->dateOfBirth ?? $this->dateOfBirth,
            gender: $other->gender ?? $this->gender,
            email: $other->email ?? $this->email,
            password: $other->password ?? $this->password,
        );
    }
}

