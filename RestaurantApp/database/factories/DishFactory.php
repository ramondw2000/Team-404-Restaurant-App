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
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->optional()->sentence(),
            'price' => $this->faker->randomFloat(2, 3, 30),
            'category' => $this->faker->randomElement(['Starters', 'Mains', 'Desserts', 'Drinks', 'Sides']),
            'allergens' => [],
            'dietary' => [],
            'color' => '#309bcf',
            'photo_path' => null,
        ];
    }
}
