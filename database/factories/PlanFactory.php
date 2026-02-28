<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->randomElement(['Starter', 'Enterprise', 'Basic']),
            'description' => fake()->sentence,
            'days' => fake()->randomElement([30, 365, 99999]),
            'price' => fake()->randomFloat(2, 0, 1000),
            'data' => [
                'workspace_limit' => ['value' => $value = fake()->numberBetween(500, 1000), 'overview' => 'Workspaces Limit: ' . $value],
                'workspace_module_limit' => ['value' => $value = fake()->numberBetween(500, 1000), 'overview' => 'Workspaces Module Limit: ' . $value],
                'workspace_member_limit' => ['value' => $value = fake()->numberBetween(500, 1000), 'overview' => 'Workspaces Member Limit: ' . $value],
                'ai_credit_limit' => ['value' => $value = fake()->numberBetween(500, 1000), 'overview' => 'Ai Credit Limit: ' . $value],
                'storage_limit' => ['value' => $value = fake()->numberBetween(500, 1000), 'overview' => 'Storage Limit: ' . $value],
            ],
            'is_featured' => fake()->boolean,
            'is_recommended' => fake()->boolean,
            'is_trial' => fake()->boolean,
            'status' => 1,
            'trial_days' => fake()->numberBetween(0, 30)
        ];
    }
}
