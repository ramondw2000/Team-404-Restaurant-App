<?php

namespace Database\Factories;

use App\Models\FloorPlan;
use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FloorPlan>
 */
class FloorPlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'background_image_id' => Image::factory(),
        ];
    }

    public function withoutBackground(): static
    {
        return $this->state(fn (array $attributes) => [
            'background_image_id' => null,
        ]);
    }
}
