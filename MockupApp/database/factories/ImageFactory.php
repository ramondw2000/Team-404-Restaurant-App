<?php

namespace Database\Factories;

use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Image>
 */
class ImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'filename' => $this->faker->uuid().'.png',
            'original_filename' => $this->faker->word().'.png',
            'path' => 'images/'.$this->faker->uuid().'.png',
            'mime_type' => 'image/png',
            'size' => $this->faker->numberBetween(10000, 500000),
        ];
    }
}
