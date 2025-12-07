<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Database\Seeder;

class TimesheetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users and projects
        $users = User::all();
        $projects = Project::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        if ($projects->isEmpty()) {
            $this->command->warn('No projects found. Please run ProjectSeeder first.');
            return;
        }

        // Create timesheets for each user
        foreach ($users as $user) {
            // Get projects assigned to this user
            $userProjects = $user->projects;

            // If user has no projects, assign them to a random project for timesheet purposes
            if ($userProjects->isEmpty()) {
                $userProjects = $projects->random(1);
            }

            // Create 5-10 timesheets per user
            $timesheetCount = rand(5, 10);

            for ($i = 0; $i < $timesheetCount; $i++) {
                $project = $userProjects->random();

                Timesheet::factory()->create([
                    'user_id' => $user->id,
                    'project_id' => $project->id,
                    'date' => now()->subDays(rand(1, 90))->format('Y-m-d'),
                ]);
            }
        }
    }
}
