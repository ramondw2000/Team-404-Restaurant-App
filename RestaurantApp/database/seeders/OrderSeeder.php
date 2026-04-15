<?php

namespace Database\Seeders;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Models\Dish;
use App\Models\FloorPlanElement;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $elements = FloorPlanElement::all();
        $dishes = Dish::all();

        if ($elements->isEmpty() || $dishes->isEmpty()) {
            return;
        }

        foreach ($elements->random(min(4, $elements->count())) as $element) {
            $order = Order::create([
                'floor_plan_element_id' => $element->id,
                'status' => OrderStatus::Completed,
                'notes' => null,
            ]);

            $selectedDishes = $dishes->random(min(3, $dishes->count()));
            foreach ($selectedDishes as $dish) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'dish_id' => $dish->id,
                    'quantity' => rand(1, 3),
                    'unit_price' => $dish->price,
                    'notes' => null,
                    'status' => OrderItemStatus::Served,
                    'course' => 1,
                ]);
            }
        }
    }
}
