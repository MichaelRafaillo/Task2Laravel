<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Timesheet>
 */
class TimesheetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'project_id' => \App\Models\Project::factory(),
            'task_name' => fake()->sentence(),
            'date' => fake()->dateTimeBetween('-90 days', 'now')->format('Y-m-d'),
            'hours' => fake()->randomFloat(2, 0.5, 8.0),
        ];
    }
}
