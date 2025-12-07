<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    private function createAuthenticatedUser(): array
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        return ['user' => $user, 'token' => $token];
    }

    /**
     * Test unauthenticated user cannot access project endpoints.
     */
    public function test_unauthenticated_user_cannot_access_project_endpoints(): void
    {
        $response = $this->getJson('/api/projects');
        $response->assertStatus(401);

        $response = $this->postJson('/api/projects', []);
        $response->assertStatus(401);
    }

    /**
     * Test authenticated user can create a new project.
     */
    public function test_authenticated_user_can_create_project(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();

        $projectData = [
            'name' => 'New Project',
            'department' => 'Engineering',
            'start_date' => '2024-01-01',
            'status' => 'active',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/projects', $projectData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'department',
                    'status',
                ],
            ]);

        $this->assertDatabaseHas('projects', [
            'name' => 'New Project',
            'department' => 'Engineering',
        ]);
    }

    /**
     * Test authenticated user can get all projects.
     */
    public function test_authenticated_user_can_get_all_projects(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();
        Project::factory()->count(3)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/projects');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'department',
                        'status',
                    ],
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test authenticated user can get a specific project by ID.
     */
    public function test_authenticated_user_can_get_project_by_id(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();
        $project = Project::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/projects/{$project->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'department',
                    'status',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $project->id,
                    'name' => $project->name,
                ],
            ]);
    }

    /**
     * Test authenticated user can filter projects by name.
     */
    public function test_authenticated_user_can_filter_projects_by_name(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();
        Project::factory()->create(['name' => 'Project Alpha']);
        Project::factory()->create(['name' => 'Project Beta']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/projects?name=Alpha');

        $response->assertStatus(200);
        $projects = $response->json('data');
        $this->assertNotEmpty($projects);
        foreach ($projects as $project) {
            $this->assertStringContainsString('Alpha', $project['name']);
        }
    }

    /**
     * Test authenticated user can filter projects by status.
     */
    public function test_authenticated_user_can_filter_projects_by_status(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();
        Project::factory()->create(['status' => 'active']);
        Project::factory()->create(['status' => 'completed']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/projects?status=active');

        $response->assertStatus(200);
        $projects = $response->json('data');
        foreach ($projects as $project) {
            $this->assertEquals('active', $project['status']);
        }
    }

    /**
     * Test authenticated user can filter projects with multiple filters (AND operation).
     */
    public function test_authenticated_user_can_filter_projects_with_multiple_filters(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();
        Project::factory()->create([
            'name' => 'Project Alpha',
            'department' => 'Engineering',
            'status' => 'active',
        ]);
        Project::factory()->create([
            'name' => 'Project Beta',
            'department' => 'Engineering',
            'status' => 'completed',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/projects?department=Engineering&status=active');

        $response->assertStatus(200);
        $projects = $response->json('data');
        foreach ($projects as $project) {
            $this->assertEquals('Engineering', $project['department']);
            $this->assertEquals('active', $project['status']);
        }
    }

    /**
     * Test authenticated user can update a project.
     */
    public function test_authenticated_user_can_update_project(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();
        $project = Project::factory()->create(['name' => 'Original Name']);

        $updateData = [
            'id' => $project->id,
            'name' => 'Updated Name',
            'status' => 'completed',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/projects/update', $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $project->id,
                    'name' => 'Updated Name',
                    'status' => 'completed',
                ],
            ]);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Name',
            'status' => 'completed',
        ]);
    }

    /**
     * Test authenticated user can delete a project.
     */
    public function test_authenticated_user_can_delete_project(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();
        $project = Project::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/projects/delete', ['id' => $project->id]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Project deleted successfully',
            ]);

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    /**
     * Test project creation fails with validation errors.
     */
    public function test_project_creation_fails_with_invalid_data(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/projects', [
                'name' => '',
                'status' => 'invalid-status',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'department', 'start_date', 'status']);
    }

    /**
     * Test getting non-existent project returns 404.
     */
    public function test_getting_nonexistent_project_returns_404(): void
    {
        ['token' => $token] = $this->createAuthenticatedUser();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/projects/99999');

        $response->assertStatus(404);
    }
}

