<?php

namespace Database\Factories;

use App\Enums\TableStatus;
use App\Models\FloorPlan;
use App\Models\FloorPlanElement;
use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FloorPlanElement>
 */
class FloorPlanElementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'floor_plan_id' => FloorPlan::factory(),
            'image_id' => Image::factory(),
            'x' => $this->faker->randomFloat(4, 0, 80),
            'y' => $this->faker->randomFloat(4, 0, 80),
            'width' => $this->faker->randomFloat(4, 5, 20),
            'height' => $this->faker->randomFloat(4, 5, 20),
            'rotation' => 0,
            'z_index' => $this->faker->numberBetween(0, 10),
            'is_table' => false,
            'table_name' => null,
            'seat_count' => null,
            'status' => null,
        ];
    }

    public function asTable(?string $name = null): static
    {
        return $this->state(fn (array $attributes) => [
            'is_table' => true,
            'table_name' => $name ?? 'Table '.$this->faker->numberBetween(1, 20),
            'seat_count' => $this->faker->numberBetween(2, 8),
            'status' => $this->faker->randomElement(TableStatus::cases())->value,
        ]);
    }

    public function withStatus(TableStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'is_table' => true,
            'table_name' => $attributes['table_name'] ?? 'Table '.$this->faker->numberBetween(1, 20),
            'seat_count' => $attributes['seat_count'] ?? 4,
            'status' => $status->value,
        ]);
    }
}
