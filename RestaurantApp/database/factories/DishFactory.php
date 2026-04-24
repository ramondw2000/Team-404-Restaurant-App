<?php

namespace Database\Factories;

use App\Models\Dish;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dish>
 */
class DishFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'price' => fake()->randomFloat(2, 3, 30),
            'color' => '#309bcf',
            'photo_path' => null,
            'is_available' => true,
            'is_bar_item' => false,
        ];
    }

    public function barItem(): static
    {
        return $this->state(['is_bar_item' => true]);
    }
}
