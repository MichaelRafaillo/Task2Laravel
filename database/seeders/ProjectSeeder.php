<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users to assign them to projects
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        // Create projects
        $projects = Project::factory(15)->create();

        // Assign users to projects (many-to-many relationship)
        foreach ($projects as $project) {
            // Assign 2-5 random users to each project
            $projectUsers = $users->random(rand(2, min(5, $users->count())));
            $project->users()->attach($projectUsers->pluck('id')->toArray());
        }
    }
}
