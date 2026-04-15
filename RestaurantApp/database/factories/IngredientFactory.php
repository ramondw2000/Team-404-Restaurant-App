<?php

namespace Database\Factories;

use App\Models\Ingredient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ingredient>
 */
class IngredientFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'allergens' => [],
            'dietary' => [],
        ];
    }

    public function vegan(): static
    {
        return $this->state(['dietary' => ['vegan']]);
    }

    public function vegetarian(): static
    {
        return $this->state(['dietary' => ['vegetarian']]);
    }

    /**
     * @param  list<string>  $allergens
     */
    public function withAllergens(array $allergens): static
    {
        return $this->state(['allergens' => $allergens]);
    }
}
