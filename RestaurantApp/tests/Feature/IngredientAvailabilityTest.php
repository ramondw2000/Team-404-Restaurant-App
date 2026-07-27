<?php

use App\Livewire\Dishes\IngredientLibrary;
use App\Livewire\Orders\BarOrderPage;
use App\Livewire\Orders\DishIngredientsModal;
use App\Livewire\Orders\OrderPage;
use App\Models\Dish;
use App\Models\FloorPlanElement;
use App\Models\Ingredient;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    (new RoleSeeder)->run();
});

function chefUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('chef');

    return $user;
}

function serverUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('server');

    return $user;
}

// ── Ingredient model ──────────────────────────────────────────

it('defaults ingredient availability to true', function () {
    $ingredient = Ingredient::factory()->create();

    expect($ingredient->is_available)->toBeTrue();
});

it('persists ingredient availability toggle', function () {
    $ingredient = Ingredient::factory()->create();

    $ingredient->update(['is_available' => false]);

    expect($ingredient->fresh()->is_available)->toBeFalse();
});

it('available scope returns only available ingredients', function () {
    Ingredient::factory()->create(['name' => 'Available One']);
    Ingredient::factory()->unavailable()->create(['name' => 'Unavailable One']);

    $names = Ingredient::available()->pluck('name')->all();

    expect($names)->toContain('Available One')
        ->and($names)->not->toContain('Unavailable One');
});

// ── Dish::isOutOfStock accessor ───────────────────────────────

it('marks dish out of stock when any ingredient is unavailable', function () {
    $available = Ingredient::factory()->create();
    $unavailable = Ingredient::factory()->unavailable()->create();

    $dish = Dish::factory()->create();
    $dish->ingredients()->attach([$available->id, $unavailable->id]);

    expect($dish->fresh()->is_out_of_stock)->toBeTrue();
});

it('marks dish in stock when all ingredients are available', function () {
    $first = Ingredient::factory()->create();
    $second = Ingredient::factory()->create();

    $dish = Dish::factory()->create();
    $dish->ingredients()->attach([$first->id, $second->id]);

    expect($dish->fresh()->is_out_of_stock)->toBeFalse();
});

it('treats a dish with zero ingredients as in stock', function () {
    $dish = Dish::factory()->create();

    expect($dish->fresh()->is_out_of_stock)->toBeFalse();
});

it('marks dish out of stock when is_available is false regardless of ingredients', function () {
    $ingredient = Ingredient::factory()->create();
    $dish = Dish::factory()->create(['is_available' => false]);
    $dish->ingredients()->attach($ingredient->id);

    expect($dish->fresh()->is_out_of_stock)->toBeTrue();
});

// ── IngredientLibrary toggle ──────────────────────────────────

it('lets a chef toggle ingredient availability from the library', function () {
    $user = chefUser();
    $ingredient = Ingredient::factory()->create();

    Livewire::actingAs($user)
        ->test(IngredientLibrary::class)
        ->call('toggleAvailability', $ingredient->id);

    expect($ingredient->fresh()->is_available)->toBeFalse();
});

it('forbids a server from toggling ingredient availability', function () {
    $user = serverUser();
    $ingredient = Ingredient::factory()->create(['is_available' => true]);

    Livewire::actingAs($user)
        ->test(IngredientLibrary::class)
        ->call('toggleAvailability', $ingredient->id)
        ->assertStatus(403);

    expect($ingredient->fresh()->is_available)->toBeTrue();
});

// ── DishIngredientsModal ──────────────────────────────────────

it('loads ingredient list with availability flags when opened', function () {
    $available = Ingredient::factory()->create(['name' => 'Mozzarella']);
    $unavailable = Ingredient::factory()->unavailable()->create(['name' => 'Tomato']);

    $dish = Dish::factory()->create(['name' => 'Caprese']);
    $dish->ingredients()->attach([$available->id, $unavailable->id]);

    Livewire::test(DishIngredientsModal::class)
        ->call('open', $dish->id)
        ->assertSet('dishId', $dish->id)
        ->assertSet('dishName', 'Caprese')
        ->assertDispatched('open-modal', 'dish-ingredients');
});

// ── Cart placeOrder guard ─────────────────────────────────────

it('rejects table order containing an out-of-stock dish', function () {
    $user = serverUser();
    $table = FloorPlanElement::factory()->create();

    $ingredient = Ingredient::factory()->unavailable()->create();
    $dish = Dish::factory()->create(['name' => 'Pasta']);
    $dish->ingredients()->attach($ingredient->id);

    Livewire::actingAs($user)
        ->test(OrderPage::class, ['floorPlanElement' => $table])
        ->call('placeOrder', [
            ['dish_id' => $dish->id, 'qty' => 1, 'notes' => ''],
        ], null)
        ->assertHasErrors('cart');
});

it('rejects bar order containing an out-of-stock dish', function () {
    $user = serverUser();

    $ingredient = Ingredient::factory()->unavailable()->create();
    $dish = Dish::factory()->barItem()->create(['name' => 'Cocktail']);
    $dish->ingredients()->attach($ingredient->id);

    Livewire::actingAs($user)
        ->test(BarOrderPage::class)
        ->call('placeOrder', [
            ['dish_id' => $dish->id, 'qty' => 1, 'notes' => ''],
        ], null)
        ->assertHasErrors('cart');
});
