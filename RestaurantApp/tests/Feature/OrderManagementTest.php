<?php

use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Livewire\Orders\OrderPage;
use App\Livewire\TableManagement;
use App\Models\Dish;
use App\Models\FloorPlanElement;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

beforeEach(function () {
    (new RoleSeeder)->run();
});

function orderUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('management');

    return $user;
}

// ── Route permission gating ────────────────────────────────────

it('redirects unauthenticated users away from orders.create', function () {
    $element = FloorPlanElement::factory()->create();

    $this->get(route('orders.create', $element))
        ->assertRedirect(route('login'));
});

it('denies access to orders.create for users without Create Order permission', function () {
    $user = User::factory()->create();
    $user->assignRole('chef');

    $element = FloorPlanElement::factory()->create();

    $this->actingAs($user)
        ->get(route('orders.create', $element))
        ->assertForbidden();
});

it('allows access to orders.create for users with Create Order permission', function () {
    $user = orderUser();
    $element = FloorPlanElement::factory()->create();

    $this->actingAs($user)
        ->get(route('orders.create', $element))
        ->assertOk();
});

// ── OrderPage mount ────────────────────────────────────────────

it('creates a draft order on mount when no active order exists', function () {
    $element = FloorPlanElement::factory()->create();

    Livewire::actingAs(orderUser())
        ->test(OrderPage::class, ['floorPlanElement' => $element])
        ->assertSet('table.id', $element->id);

    expect(Order::where('floor_plan_element_id', $element->id)
        ->where('status', OrderStatus::Draft->value)
        ->exists()
    )->toBeTrue();
});

it('reuses an existing draft order on mount', function () {
    $element = FloorPlanElement::factory()->create();
    $existing = Order::factory()->draft()->create(['floor_plan_element_id' => $element->id]);

    $component = Livewire::actingAs(orderUser())
        ->test(OrderPage::class, ['floorPlanElement' => $element]);

    expect($component->get('orderId'))->toBe($existing->id);
    expect(Order::where('floor_plan_element_id', $element->id)->count())->toBe(1);
});

// ── placeOrder ─────────────────────────────────────────────────

it('persists items and transitions order to active when placing order', function () {
    $element = FloorPlanElement::factory()->create();
    $dish = Dish::factory()->create(['price' => 18.50]);

    $component = Livewire::actingAs(orderUser())
        ->test(OrderPage::class, ['floorPlanElement' => $element]);

    $orderId = $component->get('orderId');

    $component->call('placeOrder', [
        ['dish_id' => $dish->id, 'qty' => 2, 'notes' => 'No onions'],
    ], 'Extra napkins');

    $order = Order::find($orderId);
    expect($order->status)->toBe(OrderStatus::Active);
    expect($order->notes)->toBe('Extra napkins');
    expect($order->items()->count())->toBe(1);
    expect($order->items()->first()->quantity)->toBe(2);
    expect($order->items()->first()->notes)->toBe('No onions');
});

// ── Table status automation via observer ───────────────────────

it('sets table status to Occupied when an active order is created', function () {
    $element = FloorPlanElement::factory()->available()->create();

    Order::create([
        'floor_plan_element_id' => $element->id,
        'status' => OrderStatus::Active,
    ]);

    expect($element->fresh()->status)->toBe(TableStatus::Occupied);
});

it('sets table status back to Available when order is completed', function () {
    $element = FloorPlanElement::factory()->occupied()->create();

    $order = Order::create([
        'floor_plan_element_id' => $element->id,
        'status' => OrderStatus::Active,
    ]);

    $order->update(['status' => OrderStatus::Completed]);

    expect($element->fresh()->status)->toBe(TableStatus::Available);
});

it('sets table status back to Available when order is cancelled', function () {
    $element = FloorPlanElement::factory()->occupied()->create();

    $order = Order::create([
        'floor_plan_element_id' => $element->id,
        'status' => OrderStatus::Active,
    ]);

    $order->update(['status' => OrderStatus::Cancelled]);

    expect($element->fresh()->status)->toBe(TableStatus::Available);
});

// ── Deletion guard ─────────────────────────────────────────────

it('blocks soft-deletion of a floor plan element with an active order', function () {
    $element = FloorPlanElement::factory()->create();
    Order::factory()->active()->create(['floor_plan_element_id' => $element->id]);

    expect(fn () => $element->delete())
        ->toThrow(ValidationException::class);

    expect(FloorPlanElement::find($element->id))->not->toBeNull();
});

it('allows deletion of a floor plan element with only completed orders', function () {
    $element = FloorPlanElement::factory()->create();
    Order::factory()->completed()->create(['floor_plan_element_id' => $element->id]);

    $element->delete();

    expect(FloorPlanElement::find($element->id))->toBeNull();
});

// ── TableManagement Accept Order button ───────────────────────

it('accept order navigates directly to order page when no active order exists', function () {
    $element = FloorPlanElement::factory()->create();

    Livewire::actingAs(orderUser())
        ->test(TableManagement::class)
        ->call('acceptOrder', $element->id)
        ->assertRedirect(route('orders.create', $element));
});

it('accept order shows resume confirmation when draft order already exists', function () {
    $element = FloorPlanElement::factory()->create();
    Order::factory()->draft()->create(['floor_plan_element_id' => $element->id]);

    Livewire::actingAs(orderUser())
        ->test(TableManagement::class)
        ->call('acceptOrder', $element->id)
        ->assertSet('showResumeOrderConfirm', true)
        ->assertSet('pendingOrderElementId', $element->id);
});

it('resumeOrder redirects to order page with existing draft', function () {
    $element = FloorPlanElement::factory()->create();
    Order::factory()->draft()->create(['floor_plan_element_id' => $element->id]);

    Livewire::actingAs(orderUser())
        ->test(TableManagement::class)
        ->call('acceptOrder', $element->id)
        ->call('resumeOrder')
        ->assertRedirect(route('orders.create', $element));
});

it('startNewOrder cancels the existing draft and redirects', function () {
    $element = FloorPlanElement::factory()->create();
    $draft = Order::factory()->draft()->create(['floor_plan_element_id' => $element->id]);

    Livewire::actingAs(orderUser())
        ->test(TableManagement::class)
        ->call('acceptOrder', $element->id)
        ->call('startNewOrder')
        ->assertRedirect(route('orders.create', $element));

    expect($draft->fresh()->status)->toBe(OrderStatus::Cancelled);
});

it('dismissResumeConfirm hides the modal', function () {
    $element = FloorPlanElement::factory()->create();
    Order::factory()->draft()->create(['floor_plan_element_id' => $element->id]);

    Livewire::actingAs(orderUser())
        ->test(TableManagement::class)
        ->call('acceptOrder', $element->id)
        ->call('dismissResumeConfirm')
        ->assertSet('showResumeOrderConfirm', false)
        ->assertSet('pendingOrderElementId', null);
});

// ── initialCart ───────────────────────────────────────────────

it('initialCart is empty for a brand new draft order', function () {
    $element = FloorPlanElement::factory()->create();

    $component = Livewire::actingAs(orderUser())
        ->test(OrderPage::class, ['floorPlanElement' => $element]);

    expect($component->get('initialCart'))->toBe([]);
});

it('initialCart is seeded with existing items when resuming a draft', function () {
    $element = FloorPlanElement::factory()->create();
    $dish    = Dish::factory()->create(['price' => 12.50]);
    $order   = Order::factory()->draft()->create(['floor_plan_element_id' => $element->id]);

    OrderItem::factory()->create([
        'order_id'   => $order->id,
        'dish_id'    => $dish->id,
        'quantity'   => 3,
        'unit_price' => 12.50,
        'notes'      => 'Extra sauce',
    ]);

    $component = Livewire::actingAs(orderUser())
        ->test(OrderPage::class, ['floorPlanElement' => $element]);

    $cart = $component->get('initialCart');

    expect($cart)->toHaveKey($dish->id);
    expect($cart[$dish->id]['qty'])->toBe(3);
    expect($cart[$dish->id]['price'])->toBe(12.50);
    expect($cart[$dish->id]['notes'])->toBe('Extra sauce');
    expect($cart[$dish->id]['name'])->toBe($dish->name);
});

// ── Filter bar ─────────────────────────────────────────────────

it('toggleDietaryFilter adds and removes a dietary flag', function () {
    $element = FloorPlanElement::factory()->create();

    Livewire::actingAs(orderUser())
        ->test(OrderPage::class, ['floorPlanElement' => $element])
        ->call('toggleDietaryFilter', 'vegetarian')
        ->assertSet('dietaryFilters', ['vegetarian'])
        ->call('toggleDietaryFilter', 'vegetarian')
        ->assertSet('dietaryFilters', []);
});

it('toggleAllergenFilter adds and removes an allergen', function () {
    $element = FloorPlanElement::factory()->create();

    Livewire::actingAs(orderUser())
        ->test(OrderPage::class, ['floorPlanElement' => $element])
        ->call('toggleAllergenFilter', 'gluten')
        ->assertSet('allergenFilters', ['gluten'])
        ->call('toggleAllergenFilter', 'gluten')
        ->assertSet('allergenFilters', []);
});

it('multiple dietary filters can be active simultaneously', function () {
    $element = FloorPlanElement::factory()->create();

    Livewire::actingAs(orderUser())
        ->test(OrderPage::class, ['floorPlanElement' => $element])
        ->call('toggleDietaryFilter', 'vegetarian')
        ->call('toggleDietaryFilter', 'vegan')
        ->assertSet('dietaryFilters', ['vegetarian', 'vegan']);
});
