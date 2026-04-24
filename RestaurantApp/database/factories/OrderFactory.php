<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\FloorPlanElement;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'floor_plan_element_id' => FloorPlanElement::factory(),
            'status' => OrderStatus::Draft,
            'origin' => 'restaurant',
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function bar(): static
    {
        return $this->state(['origin' => 'bar', 'floor_plan_element_id' => null]);
    }

    public function draft(): static
    {
        return $this->state(['status' => OrderStatus::Draft]);
    }

    public function active(): static
    {
        return $this->state(['status' => OrderStatus::Active]);
    }

    public function completed(): static
    {
        return $this->state(['status' => OrderStatus::Completed]);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => OrderStatus::Cancelled]);
    }
}
