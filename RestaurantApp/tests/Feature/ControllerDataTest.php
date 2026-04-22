<?php

use App\Livewire\Dishes\DishesPage;
use App\Models\Dish;
use App\Models\FloorPlanElement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    (new RoleSeeder)->run();
    $this->user = User::factory()->create();
    $this->user->assignRole('management');
    $this->actingAs($this->user);
});

it('dishes page renders as Livewire component', function () {
    $response = $this->get(route('dishes'));
    $response->assertOk();
    $response->assertSeeLivewire(DishesPage::class);
});

it('KitchenOrderController passes orders and computed counts', function () {
    $element = FloorPlanElement::factory()->create();
    $dish = Dish::factory()->create();
    $order = Order::factory()->active()->create(['floor_plan_element_id' => $element->id]);
    OrderItem::factory()->create(['order_id' => $order->id, 'dish_id' => $dish->id]);

    $response = $this->get(route('kitchen-orders'));

    $response->assertOk();
    $response->assertViewHas('allergenConfig');
    $response->assertViewHas('orders');
    $response->assertViewHas('countActive');
    $response->assertViewHas('countCompleted');
    $response->assertViewHas('totalPending');
    $response->assertViewHas('totalReady');

    $orders = $response->viewData('orders');
    expect($orders)->toBeArray()->not->toBeEmpty();
    expect($orders[0])->toHaveKeys(['id', 'type', 'dishes', 'cnt_pending', 'cnt_total', 'overall']);
});

it('AccountController passes users, roleConfig, and counts', function () {
    $response = $this->get(route('accounts.index'));

    $response->assertOk();
    $response->assertViewHas('users');
    $response->assertViewHas('roleConfig');
    $response->assertViewHas('counts');

    $counts = $response->viewData('counts');
    expect($counts)->toBeArray()->toHaveKeys(['all', 'management', 'server', 'chef', 'receptionist', 'bartender', 'barista', 'maintenance_crew']);
});
