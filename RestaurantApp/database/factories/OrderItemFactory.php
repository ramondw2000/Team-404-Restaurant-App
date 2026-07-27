<?php

namespace Database\Factories;

use App\Enums\OrderItemStatus;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 5, 35);

        return [
            'order_id' => Order::factory(),
            'dish_id' => Dish::factory(),
            'quantity' => fake()->numberBetween(1, 5),
            'unit_price' => $price,
            'notes' => fake()->optional()->sentence(),
            'status' => OrderItemStatus::Pending,
            'course' => 1,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => OrderItemStatus::Pending]);
    }

    public function preparing(): static
    {
        return $this->state(['status' => OrderItemStatus::Preparing]);
    }

    public function ready(): static
    {
        return $this->state(['status' => OrderItemStatus::Ready]);
    }

    public function served(): static
    {
        return $this->state(['status' => OrderItemStatus::Served]);
    }
}
