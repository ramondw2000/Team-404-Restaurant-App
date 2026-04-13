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

it('OrderManagementController passes dishes, allergenConfig, tables, and categories', function () {
    $response = $this->get(route('ordermanagement'));

    $response->assertOk();
    $response->assertViewHas('dishes');
    $response->assertViewHas('allergenConfig');
    $response->assertViewHas('tables');
    $response->assertViewHas('categories');

    expect($response->viewData('tables'))->toBeArray()->not->toBeEmpty();
    expect($response->viewData('categories'))->toBeArray()->toContain('Starters', 'Mains', 'Desserts');
});

it('KitchenOrderController passes orders and computed counts', function () {
    $element = FloorPlanElement::factory()->create();
    $dish    = Dish::factory()->create();
    $order   = Order::factory()->active()->create(['floor_plan_element_id' => $element->id]);
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
    expect($counts)->toBeArray()->toHaveKeys(['all', 'management', 'server', 'chef', 'receptionist', 'bar_staff', 'maintenance_crew']);
});

it('allergenConfig is consistent across controllers using shared config', function () {
    $orderMgmtResponse = $this->get(route('ordermanagement'));
    $kitchenResponse = $this->get(route('kitchen-orders'));

    expect($orderMgmtResponse->viewData('allergenConfig'))
        ->toBe($kitchenResponse->viewData('allergenConfig'));
});
