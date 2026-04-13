<?php

use App\Livewire\Dishes\DishesPage;
use App\Livewire\Dishes\DishGrid;
use App\Livewire\Dishes\DishSheet;
use App\Livewire\Dishes\IngredientLibrary;
use App\Livewire\Dishes\MenuView;
use App\Livewire\Dishes\Sidebar;
use App\Models\Dish;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    (new RoleSeeder)->run();
});

function dishesUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('management');

    return $user;
}

// ── Model Relationships ────────────────────────────────────────

it('derives allergens as union of ingredient allergens', function () {
    $gluten = Ingredient::factory()->withAllergens(['gluten'])->create();
    $milk = Ingredient::factory()->withAllergens(['milk'])->create();

    $dish = Dish::factory()->create();
    $dish->ingredients()->attach([$gluten->id, $milk->id]);

    expect($dish->allergens)->toContain('gluten', 'milk')
        ->and($dish->allergens)->toHaveCount(2);
});

it('derives dietary as intersection of ingredient dietary', function () {
    $vegan = Ingredient::factory()->vegan()->create();
    $vegetarian = Ingredient::factory()->vegetarian()->create();

    $dish = Dish::factory()->create();
    $dish->ingredients()->attach([$vegan->id, $vegetarian->id]);

    expect($dish->dietary)->toContain('vegetarian')
        ->and($dish->dietary)->not->toContain('vegan');
});

it('marks dish as vegan only when all ingredients are vegan', function () {
    $vegan1 = Ingredient::factory()->vegan()->create();
    $vegan2 = Ingredient::factory()->vegan()->create();

    $dish = Dish::factory()->create();
    $dish->ingredients()->attach([$vegan1->id, $vegan2->id]);

    expect($dish->dietary)->toContain('vegan', 'vegetarian');
});

it('returns empty dietary when dish has no ingredients', function () {
    $dish = Dish::factory()->create();

    expect($dish->dietary)->toBe([]);
});

it('returns empty allergens when dish has no ingredients', function () {
    $dish = Dish::factory()->create();

    expect($dish->allergens)->toBe([]);
});

it('blocks ingredient deletion when in use', function () {
    $user = dishesUser();
    $ingredient = Ingredient::factory()->create();
    $dish = Dish::factory()->create();
    $dish->ingredients()->attach($ingredient->id);

    Livewire::actingAs($user)
        ->test(IngredientLibrary::class)
        ->call('deleteIngredient', $ingredient->id)
        ->assertHasErrors('delete');

    expect(Ingredient::find($ingredient->id))->not->toBeNull();
});

it('allows ingredient deletion when not in use', function () {
    $user = dishesUser();
    $ingredient = Ingredient::factory()->create();

    Livewire::actingAs($user)
        ->test(IngredientLibrary::class)
        ->call('deleteIngredient', $ingredient->id);

    expect(Ingredient::find($ingredient->id))->toBeNull();
});

it('cascades dish deletion to pivot tables', function () {
    $ingredient = Ingredient::factory()->create();
    $menu = Menu::factory()->create();
    $menu->seedDefaultCategories();
    $category = $menu->categories->first();

    $dish = Dish::factory()->create();
    $dish->ingredients()->attach($ingredient->id);
    $category->dishes()->attach($dish->id, ['sort_order' => 0]);

    $dish->delete();

    expect(DB::table('dish_ingredient')->where('dish_id', $dish->id)->count())->toBe(0)
        ->and(DB::table('menu_category_dish')->where('dish_id', $dish->id)->count())->toBe(0);
});

it('removes dish assignments when category deleted', function () {
    $menu = Menu::factory()->create();
    $menu->seedDefaultCategories();
    $category = $menu->categories->first();
    $dish = Dish::factory()->create();
    $category->dishes()->attach($dish->id, ['sort_order' => 0]);

    $category->delete();

    expect(DB::table('menu_category_dish')->where('menu_category_id', $category->id)->count())->toBe(0)
        ->and(Dish::find($dish->id))->not->toBeNull();
});

// ── Menu Model ─────────────────────────────────────────────────

it('seeds default categories on new menu', function () {
    $menu = Menu::factory()->create();
    $menu->seedDefaultCategories();

    expect($menu->categories)->toHaveCount(5);
    expect($menu->categories->pluck('name')->all())->toBe(Menu::DEFAULT_CATEGORIES);
});

// ── DishesPage Component ───────────────────────────────────────

it('renders the dishes page', function () {
    $user = dishesUser();

    Livewire::actingAs($user)
        ->test(DishesPage::class)
        ->assertStatus(200)
        ->assertSee('Menu Management');
});

// ── DishGrid Component ─────────────────────────────────────────

it('renders dish grid with dishes', function () {
    $user = dishesUser();
    $dish = Dish::factory()->create(['name' => 'Test Bruschetta']);

    Livewire::actingAs($user)
        ->test(DishGrid::class)
        ->assertSee('Test Bruschetta');
});

it('filters dishes by search', function () {
    $user = dishesUser();
    Dish::factory()->create(['name' => 'Spaghetti']);
    Dish::factory()->create(['name' => 'Tiramisu']);

    Livewire::actingAs($user)
        ->test(DishGrid::class)
        ->set('search', 'Spag')
        ->assertSee('Spaghetti')
        ->assertDontSee('Tiramisu');
});

it('filters dishes free from allergens', function () {
    $user = dishesUser();

    $glutenIng = Ingredient::factory()->withAllergens(['gluten'])->create();
    $safeIng = Ingredient::factory()->create(['name' => 'Safe Tomato']);

    $glutenDish = Dish::factory()->create(['name' => 'Gluteny Pasta']);
    $glutenDish->ingredients()->attach($glutenIng->id);

    $safeDish = Dish::factory()->create(['name' => 'Safe Salad']);
    $safeDish->ingredients()->attach($safeIng->id);

    Livewire::actingAs($user)
        ->test(DishGrid::class)
        ->set('freeFromFilters', ['gluten'])
        ->assertSee('Safe Salad')
        ->assertDontSee('Gluteny Pasta');
});

it('paginates dishes', function () {
    $user = dishesUser();
    Dish::factory(15)->create();

    $component = Livewire::actingAs($user)
        ->test(DishGrid::class)
        ->set('perPage', 12);

    expect($component->get('dishes')->count())->toBe(12);
});

// ── DishSheet Component ────────────────────────────────────────

it('creates a new dish with ingredients', function () {
    $user = dishesUser();
    $ingredient = Ingredient::factory()->create();

    Livewire::actingAs($user)
        ->test(DishSheet::class)
        ->set('name', 'New Test Dish')
        ->set('price', '12.50')
        ->set('color', '#ff0000')
        ->call('addIngredient', $ingredient->id)
        ->call('save')
        ->assertDispatched('dish-saved');

    $dish = Dish::where('name', 'New Test Dish')->first();
    expect($dish)->not->toBeNull()
        ->and($dish->price)->toBe('12.50')
        ->and($dish->ingredients)->toHaveCount(1);
});

it('updates an existing dish', function () {
    $user = dishesUser();
    $dish = Dish::factory()->create(['name' => 'Old Name']);

    Livewire::actingAs($user)
        ->test(DishSheet::class, ['dishId' => $dish->id])
        ->set('name', 'Updated Name')
        ->call('save')
        ->assertDispatched('dish-saved');

    expect($dish->fresh()->name)->toBe('Updated Name');
});

it('deletes a dish', function () {
    $user = dishesUser();
    $dish = Dish::factory()->create();

    Livewire::actingAs($user)
        ->test(DishSheet::class, ['dishId' => $dish->id])
        ->call('deleteDish')
        ->assertDispatched('dish-deleted');

    expect(Dish::find($dish->id))->toBeNull();
});

it('can quick-create an ingredient from dish sheet', function () {
    $user = dishesUser();

    Livewire::actingAs($user)
        ->test(DishSheet::class)
        ->set('showNewIngredientForm', true)
        ->set('newIngredientName', 'Fresh Basil')
        ->set('newIngredientAllergens', [])
        ->set('newIngredientDietary', ['vegan'])
        ->call('createIngredient');

    $ingredient = Ingredient::where('name', 'Fresh Basil')->first();
    expect($ingredient)->not->toBeNull()
        ->and($ingredient->dietary)->toBe(['vegan']);
});

// ── IngredientLibrary Component ────────────────────────────────

it('renders ingredient library', function () {
    $user = dishesUser();
    Ingredient::factory()->create(['name' => 'Test Garlic']);

    Livewire::actingAs($user)
        ->test(IngredientLibrary::class)
        ->assertSee('Test Garlic');
});

it('creates a new ingredient', function () {
    $user = dishesUser();

    Livewire::actingAs($user)
        ->test(IngredientLibrary::class)
        ->call('openCreateForm')
        ->set('formName', 'New Pepper')
        ->set('formAllergens', [])
        ->set('formDietary', ['vegan'])
        ->call('saveIngredient');

    expect(Ingredient::where('name', 'New Pepper')->exists())->toBeTrue();
});

it('updates an ingredient', function () {
    $user = dishesUser();
    $ingredient = Ingredient::factory()->create(['name' => 'Old Spice']);

    Livewire::actingAs($user)
        ->test(IngredientLibrary::class)
        ->call('openEditForm', $ingredient->id)
        ->set('formName', 'New Spice')
        ->call('saveIngredient');

    expect($ingredient->fresh()->name)->toBe('New Spice');
});

// ── Sidebar Component ──────────────────────────────────────────

it('renders sidebar with menus', function () {
    $user = dishesUser();
    $menu = Menu::factory()->create(['name' => 'Test Lunch']);

    Livewire::actingAs($user)
        ->test(Sidebar::class)
        ->assertSee('Test Lunch');
});

it('creates a new menu from sidebar', function () {
    $user = dishesUser();

    Livewire::actingAs($user)
        ->test(Sidebar::class)
        ->set('showNewMenuForm', true)
        ->set('newMenuName', 'Dinner Menu')
        ->call('createMenu');

    $menu = Menu::where('name', 'Dinner Menu')->first();
    expect($menu)->not->toBeNull()
        ->and($menu->categories)->toHaveCount(5);
});

// ── MenuView Component ─────────────────────────────────────────

it('renders menu view', function () {
    $user = dishesUser();
    $menu = Menu::factory()->create(['name' => 'Test Menu']);
    $menu->seedDefaultCategories();

    Livewire::actingAs($user)
        ->test(MenuView::class, ['menuId' => $menu->id])
        ->assertSee('Test Menu')
        ->assertSee('Starters');
});

it('adds a category to menu', function () {
    $user = dishesUser();
    $menu = Menu::factory()->create();
    $menu->seedDefaultCategories();

    Livewire::actingAs($user)
        ->test(MenuView::class, ['menuId' => $menu->id])
        ->set('showAddCategory', true)
        ->set('newCategoryName', 'Specials')
        ->call('addCategory');

    expect($menu->fresh()->categories->pluck('name'))->toContain('Specials');
});

it('assigns a dish to category', function () {
    $user = dishesUser();
    $menu = Menu::factory()->create();
    $menu->seedDefaultCategories();
    $category = $menu->categories->first();
    $dish = Dish::factory()->create();

    Livewire::actingAs($user)
        ->test(MenuView::class, ['menuId' => $menu->id])
        ->call('openAddDishes', $category->id)
        ->call('assignDish', $dish->id);

    expect($category->fresh()->dishes)->toHaveCount(1);
});

it('removes a dish from category', function () {
    $user = dishesUser();
    $menu = Menu::factory()->create();
    $menu->seedDefaultCategories();
    $category = $menu->categories->first();
    $dish = Dish::factory()->create();
    $category->dishes()->attach($dish->id, ['sort_order' => 0]);

    Livewire::actingAs($user)
        ->test(MenuView::class, ['menuId' => $menu->id])
        ->call('removeDish', $category->id, $dish->id);

    expect($category->fresh()->dishes)->toHaveCount(0);
});

it('deletes a menu', function () {
    $user = dishesUser();
    $menu = Menu::factory()->create();
    $menu->seedDefaultCategories();

    Livewire::actingAs($user)
        ->test(MenuView::class, ['menuId' => $menu->id])
        ->call('deleteMenu')
        ->assertDispatched('menu-deleted');

    expect(Menu::find($menu->id))->toBeNull()
        ->and(MenuCategory::where('menu_id', $menu->id)->count())->toBe(0);
});

it('toggles menu status', function () {
    $user = dishesUser();
    $menu = Menu::factory()->create(['status' => 'draft']);
    $menu->seedDefaultCategories();

    Livewire::actingAs($user)
        ->test(MenuView::class, ['menuId' => $menu->id])
        ->call('toggleStatus');

    expect($menu->fresh()->status)->toBe('published');
});

it('renames a category', function () {
    $user = dishesUser();
    $menu = Menu::factory()->create();
    $menu->seedDefaultCategories();
    $category = $menu->categories->first();

    Livewire::actingAs($user)
        ->test(MenuView::class, ['menuId' => $menu->id])
        ->call('startRenameCategory', $category->id)
        ->set('renameCategoryName', 'Appetizers')
        ->call('renameCategory');

    expect($category->fresh()->name)->toBe('Appetizers');
});
