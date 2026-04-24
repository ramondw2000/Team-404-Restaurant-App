<?php

namespace App\Livewire\Dishes;

use App\Models\Dish;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\MenuCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class DishSheet extends Component
{
    use WithFileUploads;

    public ?int $dishId = null;

    // Basic info
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string')]
    public ?string $description = null;

    #[Validate('required|numeric|min:0|max:9999.99|decimal:0,2')]
    public string $price = '';

    #[Validate('nullable|string|max:7')]
    public string $color = '#309bcf';

    #[Validate('nullable|image|max:5120')]
    public $photo = null;

    public ?string $existingPhotoPath = null;

    #[Validate('boolean')]
    public bool $isAvailable = true;

    #[Validate('boolean')]
    public bool $isBarItem = false;

    // Ingredients
    /** @var list<int> */
    public array $ingredientIds = [];

    public string $ingredientSearch = '';

    public bool $showNewIngredientForm = false;

    #[Validate('required_if:showNewIngredientForm,true|string|max:255')]
    public string $newIngredientName = '';

    /** @var list<string> */
    public array $newIngredientAllergens = [];

    /** @var list<string> */
    public array $newIngredientDietary = [];

    // Menu assignments
    /** @var array<int, array{menu_id: int, category_id: int|null}> */
    public array $menuAssignments = [];

    public function mount(?int $dishId = null): void
    {
        $this->dishId = $dishId;

        if ($dishId) {
            $dish = Dish::with(['ingredients', 'menuCategories.menu'])->findOrFail($dishId);
            $this->name = $dish->name;
            $this->description = $dish->description;
            $this->price = $dish->price;
            $this->color = $dish->color;
            $this->existingPhotoPath = $dish->photo_path;
            $this->isAvailable = $dish->is_available;
            $this->isBarItem = $dish->is_bar_item;
            $this->ingredientIds = $dish->ingredients->pluck('id')->all();

            $this->menuAssignments = $dish->menuCategories->map(fn (MenuCategory $mc): array => [
                'menu_id' => $mc->menu_id,
                'category_id' => $mc->id,
            ])->values()->all();
        }
    }

    /**
     * @return Collection<int, Ingredient>
     */
    #[Computed]
    public function availableIngredients(): Collection
    {
        $query = Ingredient::query();

        if ($this->ingredientSearch !== '') {
            $query->where('name', 'like', '%'.$this->ingredientSearch.'%');
        }

        return $query->whereNotIn('id', $this->ingredientIds)
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    /**
     * @return Collection<int, Ingredient>
     */
    #[Computed]
    public function selectedIngredients(): Collection
    {
        if ($this->ingredientIds === []) {
            return new Collection;
        }

        return Ingredient::whereIn('id', $this->ingredientIds)->orderBy('name')->get();
    }

    /**
     * @return Collection<int, Menu>
     */
    #[Computed]
    public function menus(): Collection
    {
        return Menu::with('categories')->get();
    }

    /**
     * Computed allergens from selected ingredients.
     *
     * @return list<string>
     */
    #[Computed]
    public function derivedAllergens(): array
    {
        return $this->selectedIngredients
            ->pluck('allergens')
            ->flatten()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Computed dietary from selected ingredients.
     *
     * @return list<string>
     */
    #[Computed]
    public function derivedDietary(): array
    {
        $ingredients = $this->selectedIngredients;

        if ($ingredients->isEmpty()) {
            return [];
        }

        $dietary = [];

        if ($ingredients->every(fn (Ingredient $i): bool => in_array('vegetarian', $i->dietary) || in_array('vegan', $i->dietary))) {
            $dietary[] = 'vegetarian';
        }

        if ($ingredients->every(fn (Ingredient $i): bool => in_array('vegan', $i->dietary))) {
            $dietary[] = 'vegan';
        }

        return $dietary;
    }

    public function addIngredient(int $ingredientId): void
    {
        if (! in_array($ingredientId, $this->ingredientIds)) {
            $this->ingredientIds[] = $ingredientId;
        }
        $this->ingredientSearch = '';
        unset($this->availableIngredients, $this->selectedIngredients, $this->derivedAllergens, $this->derivedDietary);
    }

    public function removeIngredient(int $ingredientId): void
    {
        $this->ingredientIds = array_values(array_diff($this->ingredientIds, [$ingredientId]));
        unset($this->selectedIngredients, $this->derivedAllergens, $this->derivedDietary);
    }

    public function createIngredient(): void
    {
        $this->authorize('Edit Dishes');

        $this->validate([
            'newIngredientName' => 'required|string|max:255|unique:ingredients,name',
        ]);

        $ingredient = Ingredient::create([
            'name' => $this->newIngredientName,
            'allergens' => $this->newIngredientAllergens,
            'dietary' => $this->newIngredientDietary,
        ]);

        $this->ingredientIds[] = $ingredient->id;
        $this->newIngredientName = '';
        $this->newIngredientAllergens = [];
        $this->newIngredientDietary = [];
        $this->showNewIngredientForm = false;
        unset($this->availableIngredients, $this->selectedIngredients, $this->derivedAllergens, $this->derivedDietary);
    }

    public function addMenuAssignment(): void
    {
        $this->menuAssignments[] = ['menu_id' => 0, 'category_id' => null];
    }

    public function removeMenuAssignment(int $index): void
    {
        unset($this->menuAssignments[$index]);
        $this->menuAssignments = array_values($this->menuAssignments);
    }

    public function save(): void
    {
        $this->authorize($this->dishId ? 'Edit Dishes' : 'Add Dishes');

        $this->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0|max:9999.99|decimal:0,2',
            'color' => 'nullable|string|max:7',
            'photo' => 'nullable|image|max:5120',
            'description' => 'nullable|string',
        ]);

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'color' => $this->color ?: '#309bcf',
            'is_available' => $this->isAvailable,
            'is_bar_item' => $this->isBarItem,
        ];

        if ($this->photo) {
            if ($this->existingPhotoPath) {
                Storage::disk('public')->delete($this->existingPhotoPath);
            }
            $data['photo_path'] = $this->photo->store('dishes', 'public');
        }

        if ($this->dishId) {
            $dish = Dish::findOrFail($this->dishId);
            $dish->update($data);
        } else {
            $dish = Dish::create($data);
        }

        // Sync ingredients
        $dish->ingredients()->sync($this->ingredientIds);

        // Sync menu assignments
        $dish->menuCategories()->detach();
        $sortCounter = [];
        foreach ($this->menuAssignments as $assignment) {
            $categoryId = $assignment['category_id'] ?? null;
            if ($categoryId) {
                $sortCounter[$categoryId] = ($sortCounter[$categoryId] ?? -1) + 1;
                $dish->menuCategories()->attach($categoryId, [
                    'sort_order' => $sortCounter[$categoryId],
                ]);
            }
        }

        $this->dispatch('dish-saved');
    }

    public function deleteDish(): void
    {
        $this->authorize('Delete Dishes');

        if (! $this->dishId) {
            return;
        }

        $dish = Dish::findOrFail($this->dishId);
        $dish->delete();

        $this->dispatch('dish-deleted');
    }

    public function toggleAvailability(): void
    {
        $this->authorize('Edit Dishes');

        if (! $this->dishId) {
            return;
        }

        $dish = Dish::findOrFail($this->dishId);
        $this->isAvailable = ! $dish->is_available;
        $dish->update(['is_available' => $this->isAvailable]);

        $this->dispatch('dish-saved');
    }

    public function close(): void
    {
        $this->dispatch('close-dish-sheet');
    }

    public function render(): View
    {
        /** @var array<string, array{label: string, bg: string, icon: string}> $allergenConfig */
        $allergenConfig = config('restaurant.allergens');

        return view('livewire.dishes.dish-sheet', [
            'allergenConfig' => $allergenConfig,
        ]);
    }
}
