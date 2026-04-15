<?php

namespace Database\Factories;

use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Image>
 */
class ImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'filename' => fake()->uuid().'.png',
            'original_filename' => fake()->word().'.png',
            'path' => 'images/'.fake()->uuid().'.png',
            'mime_type' => 'image/png',
            'size' => fake()->numberBetween(10000, 500000),
            'width' => fake()->numberBetween(800, 1920),
            'height' => fake()->numberBetween(600, 1080),
        ];
    }
}
