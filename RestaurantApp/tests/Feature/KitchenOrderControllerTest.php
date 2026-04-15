<?php

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Models\Dish;
use App\Models\FloorPlanElement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    (new RoleSeeder)->run();
});

function kitchenUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('chef');

    return $user;
}

it('shows active orders on the kitchen orders page', function () {
    $element = FloorPlanElement::factory()->create(['table_name' => 'B4']);
    $dish = Dish::factory()->create(['name' => 'Osso Buco']);

    $order = Order::factory()->active()->create(['floor_plan_element_id' => $element->id]);
    OrderItem::factory()->create([
        'order_id'   => $order->id,
        'dish_id'    => $dish->id,
        'quantity'   => 2,
        'unit_price' => 18.50,
        'status'     => OrderItemStatus::Pending,
    ]);

    $this->actingAs(kitchenUser())
        ->get(route('kitchen-orders'))
        ->assertOk()
        ->assertSee('Osso Buco')
        ->assertSee('B4');
});

it('does not show completed or cancelled orders', function () {
    $element = FloorPlanElement::factory()->create();
    $dish = Dish::factory()->create(['name' => 'Hidden Dish']);

    Order::factory()->completed()->create(['floor_plan_element_id' => $element->id]);
    Order::factory()->cancelled()->create(['floor_plan_element_id' => $element->id]);

    $this->actingAs(kitchenUser())
        ->get(route('kitchen-orders'))
        ->assertOk()
        ->assertDontSee('Hidden Dish');
});

it('shows empty kitchen when no active orders exist', function () {
    $this->actingAs(kitchenUser())
        ->get(route('kitchen-orders'))
        ->assertOk()
        ->assertSee('No orders match this filter');
});

it('mark dish ready toggles item status to ready', function () {
    $element = FloorPlanElement::factory()->create();
    $dish    = Dish::factory()->create();
    $order   = Order::factory()->active()->create(['floor_plan_element_id' => $element->id]);
    $item    = OrderItem::factory()->create([
        'order_id' => $order->id,
        'dish_id'  => $dish->id,
        'status'   => OrderItemStatus::Pending,
    ]);

    $this->actingAs(kitchenUser())
        ->patch(route('kitchen-orders.dish.ready', $item))
        ->assertOk()
        ->assertJson(['status' => 'ready']);

    expect($item->fresh()->status)->toBe(OrderItemStatus::Ready);
});

it('mark dish ready toggles item status back to pending when already ready', function () {
    $element = FloorPlanElement::factory()->create();
    $dish    = Dish::factory()->create();
    $order   = Order::factory()->active()->create(['floor_plan_element_id' => $element->id]);
    $item    = OrderItem::factory()->ready()->create([
        'order_id' => $order->id,
        'dish_id'  => $dish->id,
    ]);

    $this->actingAs(kitchenUser())
        ->patch(route('kitchen-orders.dish.ready', $item))
        ->assertOk()
        ->assertJson(['status' => 'pending']);

    expect($item->fresh()->status)->toBe(OrderItemStatus::Pending);
});

it('complete order marks all items served and order completed', function () {
    $element = FloorPlanElement::factory()->create();
    $dish    = Dish::factory()->create();
    $order   = Order::factory()->active()->create(['floor_plan_element_id' => $element->id]);
    OrderItem::factory()->create(['order_id' => $order->id, 'dish_id' => $dish->id, 'status' => OrderItemStatus::Ready]);
    OrderItem::factory()->create(['order_id' => $order->id, 'dish_id' => $dish->id, 'status' => OrderItemStatus::Ready]);

    $this->actingAs(kitchenUser())
        ->patch(route('kitchen-orders.order.complete', $order))
        ->assertOk()
        ->assertJson(['status' => 'completed']);

    expect($order->fresh()->status)->toBe(OrderStatus::Completed);
    expect($order->items()->where('status', '!=', OrderItemStatus::Served->value)->count())->toBe(0);
});

it('reflects the correct order id format', function () {
    $element = FloorPlanElement::factory()->create();
    $dish = Dish::factory()->create();

    $order = Order::factory()->active()->create(['floor_plan_element_id' => $element->id]);
    OrderItem::factory()->create(['order_id' => $order->id, 'dish_id' => $dish->id]);

    $expectedId = 'ORD-' . str_pad((string) $order->id, 3, '0', STR_PAD_LEFT);

    $this->actingAs(kitchenUser())
        ->get(route('kitchen-orders'))
        ->assertOk()
        ->assertSee($expectedId);
});
