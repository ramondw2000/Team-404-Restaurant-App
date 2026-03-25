<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('management', 'web');
    $this->user = User::factory()->create();
    $this->user->assignRole('management');
    $this->actingAs($this->user);
});

it('DishController passes dishes and allergenConfig to the dishes view', function () {
    $response = $this->get(route('dishes'));

    $response->assertOk();
    $response->assertViewHas('dishes');
    $response->assertViewHas('allergenConfig');

    $dishes = $response->viewData('dishes');
    $allergenConfig = $response->viewData('allergenConfig');

    expect($dishes)->toBeArray()->not->toBeEmpty();
    expect($allergenConfig)->toBeArray()->toHaveKeys(['gluten', 'nuts', 'milk', 'wheat', 'fish', 'egg']);
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

it('allergenConfig is consistent across all controllers using shared config', function () {
    $dishResponse = $this->get(route('dishes'));
    $orderMgmtResponse = $this->get(route('ordermanagement'));
    $kitchenResponse = $this->get(route('kitchen-orders'));

    expect($dishResponse->viewData('allergenConfig'))
        ->toBe($orderMgmtResponse->viewData('allergenConfig'))
        ->toBe($kitchenResponse->viewData('allergenConfig'));
});
