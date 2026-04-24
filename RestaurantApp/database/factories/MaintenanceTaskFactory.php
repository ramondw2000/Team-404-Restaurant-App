<?php

namespace Database\Factories;

use App\Enums\MaintenanceTaskStatus;
use App\Models\MaintenanceTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceTask>
 */
class MaintenanceTaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(4),
            'location' => $this->faker->randomElement(['Kitchen', 'Bar', 'Dining room', 'Terrace', 'Staff room', 'Entrance']),
            'status' => MaintenanceTaskStatus::Pending,
            'notes' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MaintenanceTaskStatus::Completed,
            'notes' => $this->faker->sentence(),
        ]);
    }
}
