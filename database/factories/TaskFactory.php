<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [ 'title' => $this->faker->sentence,
        'description' => $this->faker->paragraph,
        'type' => $this->faker->randomElement(['bug','feature','improvement']),
         'status' => $this->faker->randomElement(['open', 'in_progress', 'completed', 'blocked']),
          'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
          'due_date' => $this->faker->optional()->date,
          'assigned_to' => User::factory(),
          'created_by' => User::factory(), ];
    }
}
