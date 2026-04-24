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

it('StatisticsController passes sales data and unsold items', function () {
    $soldDish = Dish::factory()->create(['name' => 'Sold Pasta']);
    $unsoldDish = Dish::factory()->create(['name' => 'Unsold Risotto']);
    $soldDrink = Dish::factory()->barItem()->create(['name' => 'Sold Beer']);
    $unsoldDrink = Dish::factory()->barItem()->create(['name' => 'Unsold Juice']);

    $order = Order::factory()->completed()->create();
    OrderItem::factory()->create(['order_id' => $order->id, 'dish_id' => $soldDish->id, 'quantity' => 3]);
    OrderItem::factory()->create(['order_id' => $order->id, 'dish_id' => $soldDrink->id, 'quantity' => 2]);

    $response = $this->get(route('statistics'));

    $response->assertOk();
    $response->assertViewHas('totalSales');
    $response->assertViewHas('orderCount');
    $response->assertViewHas('topItems');
    $response->assertViewHas('leastSoldDishes');
    $response->assertViewHas('topBarDrinks');
    $response->assertViewHas('leastSoldBarDrinks');
    $response->assertViewHas('totalDishRevenue');
    $response->assertViewHas('totalBarRevenue');
    $response->assertViewHas('unsoldDishes');
    $response->assertViewHas('unsoldBarDrinks');

    $unsoldDishes = $response->viewData('unsoldDishes');
    $unsoldBarDrinks = $response->viewData('unsoldBarDrinks');

    expect($unsoldDishes)->toContain('Unsold Risotto')
        ->not->toContain('Sold Pasta');
    expect($unsoldBarDrinks)->toContain('Unsold Juice')
        ->not->toContain('Sold Beer');
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
