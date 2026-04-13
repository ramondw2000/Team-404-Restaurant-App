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
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'background_image_id' => Image::factory(),
        ];
    }
}
