<?php

namespace Database\Factories;

use App\Enums\TableStatus;
use App\Models\FloorPlan;
use App\Models\FloorPlanElement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FloorPlanElement>
 */
class FloorPlanElementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'floor_plan_id' => FloorPlan::factory(),
            'shape' => fake()->randomElement(['round', 'rectangular']),
            'seat_count' => fake()->randomElement([2, 4, 6, 8, 10]),
            'x' => fake()->randomFloat(2, 5, 80),
            'y' => fake()->randomFloat(2, 5, 80),
            'width' => fake()->randomFloat(2, 5, 15),
            'height' => fake()->randomFloat(2, 5, 15),
            'rotation' => 0,
            'z_index' => fake()->numberBetween(0, 10),
            'table_name' => 'Table '.fake()->numberBetween(1, 50),
            'status' => fake()->randomElement(TableStatus::cases()),
        ];
    }

    public function round(int $seats = 4): static
    {
        return $this->state(fn () => [
            'shape' => 'round',
            'seat_count' => $seats,
        ]);
    }

    public function rectangular(int $seats = 4): static
    {
        return $this->state(fn () => [
            'shape' => 'rectangular',
            'seat_count' => $seats,
        ]);
    }

    public function available(): static
    {
        return $this->state(fn () => [
            'status' => TableStatus::Available,
        ]);
    }

    public function reserved(): static
    {
        return $this->state(fn () => [
            'status' => TableStatus::Reserved,
        ]);
    }

    public function occupied(): static
    {
        return $this->state(fn () => [
            'status' => TableStatus::Occupied,
        ]);
    }
}
