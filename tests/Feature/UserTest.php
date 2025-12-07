<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private function createAuthenticatedUser(): array
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        return ['user' => $user, 'token' => $token];
    }

    /**
     * Test unauthenticated user cannot access user endpoints.
     */
    public function test_unauthenticated_user_cannot_access_user_endpoints(): void
    {
        $response = $this->getJson('/api/users');
        $response->assertStatus(401);

        $response = $this->postJson('/api/users', []);
        $response->assertStatus(401);
    }

    /**
     * Test authenticated user can create a new user.
     */
    public function test_authenticated_user_can_create_user(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();

        $userData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'date_of_birth' => '1995-05-15',
            'gender' => 'female',
            'email' => 'jane@example.com',
            'password' => 'password123',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/users', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'first_name' => 'Jane',
        ]);
    }

    /**
     * Test authenticated user can get all users.
     */
    public function test_authenticated_user_can_get_all_users(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();
        User::factory()->count(3)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                    ],
                ],
            ]);

        $this->assertCount(4, $response->json('data')); // 3 created + 1 authenticated
    }

    /**
     * Test authenticated user can get a specific user by ID.
     */
    public function test_authenticated_user_can_get_user_by_id(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();
        $user = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ],
            ]);
    }

    /**
     * Test authenticated user can filter users by first_name.
     */
    public function test_authenticated_user_can_filter_users_by_first_name(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();
        User::factory()->create(['first_name' => 'John']);
        User::factory()->create(['first_name' => 'Jane']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/users?first_name=John');

        $response->assertStatus(200);
        $users = $response->json('data');
        $this->assertNotEmpty($users);
        foreach ($users as $user) {
            $this->assertStringContainsString('John', $user['first_name']);
        }
    }

    /**
     * Test authenticated user can filter users by gender.
     */
    public function test_authenticated_user_can_filter_users_by_gender(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();
        User::factory()->create(['gender' => 'male']);
        User::factory()->create(['gender' => 'female']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/users?gender=male');

        $response->assertStatus(200);
        $users = $response->json('data');
        $this->assertNotEmpty($users);
        foreach ($users as $user) {
            $this->assertEquals('male', $user['gender']);
        }
    }

    /**
     * Test authenticated user can filter users by multiple fields (AND operation).
     */
    public function test_authenticated_user_can_filter_users_with_multiple_filters(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();
        User::factory()->create([
            'first_name' => 'John',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
        ]);
        User::factory()->create([
            'first_name' => 'John',
            'gender' => 'female',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/users?first_name=John&gender=male');

        $response->assertStatus(200);
        $users = $response->json('data');
        foreach ($users as $user) {
            $this->assertStringContainsString('John', $user['first_name']);
            $this->assertEquals('male', $user['gender']);
        }
    }

    /**
     * Test authenticated user can update a user.
     */
    public function test_authenticated_user_can_update_user(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();
        $user = User::factory()->create(['first_name' => 'Original']);

        $updateData = [
            'id' => $user->id,
            'first_name' => 'Updated',
            'last_name' => 'Name',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/users/update', $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'first_name' => 'Updated',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Updated',
        ]);
    }

    /**
     * Test authenticated user can delete a user.
     */
    public function test_authenticated_user_can_delete_user(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();
        $user = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/users/delete', ['id' => $user->id]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User deleted successfully',
            ]);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /**
     * Test user creation fails with validation errors.
     */
    public function test_user_creation_fails_with_invalid_data(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/users', [
                'first_name' => '',
                'email' => 'invalid-email',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'email', 'password']);
    }

    /**
     * Test user update fails with validation errors.
     */
    public function test_user_update_fails_with_invalid_data(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();
        $user = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/users/update', [
                'id' => $user->id,
                'email' => 'invalid-email',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test getting non-existent user returns 404.
     */
    public function test_getting_nonexistent_user_returns_404(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/users/99999');

        $response->assertStatus(404);
    }
}

