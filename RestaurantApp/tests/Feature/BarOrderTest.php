<?php

use App\Enums\OrderStatus;
use App\Livewire\Orders\BarOrderPage;
use App\Livewire\Orders\OrderPage;
use App\Models\Dish;
use App\Models\FloorPlanElement;
use App\Models\Order;
use App\Models\User;
use App\Support\PermissionRegistry;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    (new RoleSeeder)->run();
});

function bartenderUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('bartender');

    return $user;
}

function managerUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('management');

    return $user;
}

// ── Permissions ────────────────────────────────────────────────

it('denies bar-orders.create to users without Create Bar Order permission', function () {
    $user = User::factory()->create();
    $user->assignRole('chef');

    $this->actingAs($user)
        ->get(route('bar-orders.create'))
        ->assertForbidden();
});

it('allows bar-orders.create to bartenders', function () {
    $this->actingAs(bartenderUser())
        ->get(route('bar-orders.create'))
        ->assertOk();
});

// ── Mount creates draft bar order ──────────────────────────────

it('creates a draft bar order on mount with origin=bar and no table', function () {
    $user = bartenderUser();

    Livewire::actingAs($user)->test(BarOrderPage::class);

    $order = Order::where('user_id', $user->id)->where('origin', 'bar')->first();
    expect($order)->not->toBeNull();
    expect($order->floor_plan_element_id)->toBeNull();
    expect($order->status)->toBe(OrderStatus::Draft);
});

it('reuses an existing draft bar order on mount', function () {
    $user = bartenderUser();
    $existing = Order::factory()->bar()->draft()->create(['user_id' => $user->id]);

    $component = Livewire::actingAs($user)->test(BarOrderPage::class);

    expect($component->get('orderId'))->toBe($existing->id);
});

// ── Dish filtering ─────────────────────────────────────────────

it('lists only dishes flagged as bar items on the BarOrderPage', function () {
    Dish::factory()->barItem()->create(['name' => 'Negroni']);
    Dish::factory()->create(['name' => 'Spaghetti']);

    $component = Livewire::actingAs(bartenderUser())->test(BarOrderPage::class);

    $names = collect($component->get('dishes'))->pluck('name')->all();
    expect($names)->toContain('Negroni')->not->toContain('Spaghetti');
});

// ── placeOrder ─────────────────────────────────────────────────

it('persists items and activates the bar order on placeOrder', function () {
    $drink = Dish::factory()->barItem()->create(['price' => 7.50]);

    $component = Livewire::actingAs(bartenderUser())->test(BarOrderPage::class);

    $component->call('placeOrder', [
        ['dish_id' => $drink->id, 'qty' => 2, 'notes' => 'Extra ice'],
    ], 'For terrace');

    $order = Order::find($component->get('orderId'));
    expect($order->status)->toBe(OrderStatus::Active);
    expect($order->origin)->toBe('bar');
    expect($order->floor_plan_element_id)->toBeNull();
    expect($order->items()->count())->toBe(1);
    expect($order->items()->first()->notes)->toBe('Extra ice');
});

// ── Bar tab on table OrderPage ─────────────────────────────────

it('shows bar items when barMode is active on the table OrderPage', function () {
    $element = FloorPlanElement::factory()->create();
    Dish::factory()->barItem()->create(['name' => 'Aperol Spritz']);
    Dish::factory()->create(['name' => 'Risotto']);

    $component = Livewire::actingAs(managerUser())
        ->test(OrderPage::class, ['floorPlanElement' => $element]);

    $component->call('setBarMode');

    $names = collect($component->get('dishes'))->pluck('name')->all();
    expect($names)->toContain('Aperol Spritz')->not->toContain('Risotto');
});

// ── /orders bar section ────────────────────────────────────────

it('renders standalone bar orders in the bar section of /orders', function () {
    $drink = Dish::factory()->barItem()->create(['name' => 'Mojito']);

    $order = Order::factory()->bar()->active()->create(['user_id' => bartenderUser()->id]);
    $order->items()->create([
        'dish_id' => $drink->id,
        'quantity' => 1,
        'unit_price' => $drink->price,
        'status' => 'pending',
        'course' => 1,
    ]);

    $this->actingAs(managerUser())
        ->get(route('orders'))
        ->assertOk()
        ->assertSee('Mojito');
});

it('keeps drinks out of the kitchen section', function () {
    $element = FloorPlanElement::factory()->create();
    $drink = Dish::factory()->barItem()->create(['name' => 'Negroni']);
    $food = Dish::factory()->create(['name' => 'Pizza Margherita']);

    $order = Order::factory()->active()->create(['floor_plan_element_id' => $element->id]);
    $order->items()->create([
        'dish_id' => $drink->id,
        'quantity' => 1,
        'unit_price' => $drink->price,
        'status' => 'pending',
        'course' => 1,
    ]);
    $order->items()->create([
        'dish_id' => $food->id,
        'quantity' => 1,
        'unit_price' => $food->price,
        'status' => 'pending',
        'course' => 1,
    ]);

    $this->actingAs(managerUser())
        ->get(route('orders'))
        ->assertOk()
        ->assertSee('Pizza Margherita')
        ->assertSee('Negroni');
});

// ── Permission registry ────────────────────────────────────────

it('registers Create Bar Order in the permission registry', function () {
    expect(PermissionRegistry::allNames())
        ->toContain('Create Bar Order');
});

it('grants Create Bar Order to the bartender role by default', function () {
    expect(bartenderUser()->can('Create Bar Order'))->toBeTrue();
});
