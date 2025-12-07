<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Timesheet;
use App\Models\User;
use Tests\TestCase;

class TimesheetTest extends TestCase
{
    private function createAuthenticatedUser(): array
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        return ['user' => $user, 'token' => $token];
    }

    /**
     * Test unauthenticated user cannot access timesheet endpoints.
     */
    public function test_unauthenticated_user_cannot_access_timesheet_endpoints(): void
    {
        $response = $this->getJson('/api/timesheets');
        $response->assertStatus(401);

        $response = $this->postJson('/api/timesheets', []);
        $response->assertStatus(401);
    }

    /**
     * Test authenticated user can create a new timesheet.
     */
    public function test_authenticated_user_can_create_timesheet(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $timesheetData = [
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task_name' => 'Task Description',
            'date' => '2024-01-01',
            'hours' => 8.5,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/timesheets', $timesheetData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'project_id',
                    'task_name',
                    'hours',
                ],
            ]);

        $this->assertDatabaseHas('timesheets', [
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task_name' => 'Task Description',
        ]);
    }

    /**
     * Test authenticated user can get all timesheets.
     */
    public function test_authenticated_user_can_get_all_timesheets(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();
        Timesheet::factory()->count(3)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/timesheets');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'project_id',
                        'task_name',
                        'hours',
                    ],
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test authenticated user can get a specific timesheet by ID.
     */
    public function test_authenticated_user_can_get_timesheet_by_id(): void
    {
        ['user' => $authenticatedUser, 'token' => $token] = $this->createAuthenticatedUser();
        $project = Project::factory()->create();
        $project->users()->attach($authenticatedUser->id);
        $timesheet = Timesheet::factory()->create([
            'user_id' => $authenticatedUser->id,
            'project_id' => $project->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/timesheets/{$timesheet->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'project_id',
                    'task_name',
                    'hours',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $timesheet->id,
                    'task_name' => $timesheet->task_name,
                ],
            ]);
    }

    /**
     * Test authenticated user can filter timesheets by user_id.
     */
    public function test_authenticated_user_can_filter_timesheets_by_user_id(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Timesheet::factory()->create(['user_id' => $user1->id]);
        Timesheet::factory()->create(['user_id' => $user2->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/timesheets?user_id={$user1->id}");

        $response->assertStatus(200);
        $timesheets = $response->json('data');
        $this->assertNotEmpty($timesheets);
        foreach ($timesheets as $timesheet) {
            $this->assertEquals($user1->id, $timesheet['user_id']);
        }
    }

    /**
     * Test authenticated user can filter timesheets by project_id.
     */
    public function test_authenticated_user_can_filter_timesheets_by_project_id(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();
        $project1 = Project::factory()->create();
        $project2 = Project::factory()->create();
        Timesheet::factory()->create(['project_id' => $project1->id]);
        Timesheet::factory()->create(['project_id' => $project2->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/timesheets?project_id={$project1->id}");

        $response->assertStatus(200);
        $timesheets = $response->json('data');
        foreach ($timesheets as $timesheet) {
            $this->assertEquals($project1->id, $timesheet['project_id']);
        }
    }

    /**
     * Test authenticated user can filter timesheets with multiple filters (AND operation).
     */
    public function test_authenticated_user_can_filter_timesheets_with_multiple_filters(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();
        $user = User::factory()->create();
        $project = Project::factory()->create();
        Timesheet::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task_name' => 'Task A',
        ]);
        Timesheet::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task_name' => 'Task B',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/timesheets?user_id={$user->id}&project_id={$project->id}");

        $response->assertStatus(200);
        $timesheets = $response->json('data');
        $this->assertCount(2, $timesheets);
        foreach ($timesheets as $timesheet) {
            $this->assertEquals($user->id, $timesheet['user_id']);
            $this->assertEquals($project->id, $timesheet['project_id']);
        }
    }

    /**
     * Test authenticated user can update a timesheet.
     */
    public function test_authenticated_user_can_update_timesheet(): void
    {
        ['user' => $authenticatedUser, 'token' => $token] = $this->createAuthenticatedUser();
        $project = Project::factory()->create();
        $timesheet = Timesheet::factory()->create([
            'user_id' => $authenticatedUser->id,
            'project_id' => $project->id,
            'hours' => 4.0,
        ]);

        $updateData = [
            'id' => $timesheet->id,
            'hours' => 8.0,
            'task_name' => 'Updated Task',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/timesheets/update', $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $timesheet->id,
                    'hours' => 8.0,
                    'task_name' => 'Updated Task',
                ],
            ]);

        $this->assertDatabaseHas('timesheets', [
            'id' => $timesheet->id,
            'hours' => 8.0,
            'task_name' => 'Updated Task',
        ]);
    }

    /**
     * Test authenticated user can delete a timesheet.
     */
    public function test_authenticated_user_can_delete_timesheet(): void
    {
        ['user' => $authenticatedUser, 'token' => $token] = $this->createAuthenticatedUser();
        $project = Project::factory()->create();
        $timesheet = Timesheet::factory()->create([
            'user_id' => $authenticatedUser->id,
            'project_id' => $project->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/timesheets/delete', ['id' => $timesheet->id]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Timesheet deleted successfully',
            ]);

        $this->assertDatabaseMissing('timesheets', ['id' => $timesheet->id]);
    }

    /**
     * Test timesheet creation fails with validation errors.
     */
    public function test_timesheet_creation_fails_with_invalid_data(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/timesheets', [
                'task_name' => '',
                'hours' => 25, // Invalid: exceeds max 24
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id', 'project_id', 'task_name', 'date', 'hours']);
    }

    /**
     * Test getting non-existent timesheet returns 404.
     */
    public function test_getting_nonexistent_timesheet_returns_404(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/timesheets/99999');

        $response->assertStatus(404);
    }
}

