<?php

namespace App\Livewire\Dishes;

use App\Models\Ingredient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class IngredientLibrary extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    /** @var int[] */
    public array $newIngredientIds = [];

    /** @var int[] */
    public array $updatedIngredientIds = [];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, Ingredient>
     */
    #[Computed]
    public function ingredients(): LengthAwarePaginator
    {
        $query = Ingredient::withCount('dishes');

        if ($this->search !== '') {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        if ($this->newIngredientIds !== [] || $this->updatedIngredientIds !== []) {
            $newPlaceholders = implode(',', array_fill(0, max(count($this->newIngredientIds), 1), '?'));
            $updatedPlaceholders = implode(',', array_fill(0, max(count($this->updatedIngredientIds), 1), '?'));

            $query->orderByRaw(
                "CASE WHEN id IN ({$newPlaceholders}) THEN 0 WHEN id IN ({$updatedPlaceholders}) THEN 1 ELSE 2 END",
                [...($this->newIngredientIds ?: [0]), ...($this->updatedIngredientIds ?: [0])]
            )->orderBy('name');
        } else {
            $query->orderBy('name');
        }

        return $query->paginate(20);
    }

    public function deleteIngredient(int $id): void
    {
        $ingredient = Ingredient::withCount('dishes')->findOrFail($id);

        if ($ingredient->dishes_count > 0) {
            $this->addError('delete', "Cannot delete \"{$ingredient->name}\" — used by {$ingredient->dishes_count} dish(es). Remove from all dishes first.");

            return;
        }

        $ingredient->delete();
        unset($this->ingredients);
    }

    #[On('dish-saved')]
    #[On('dish-deleted')]
    public function refreshIngredients(): void
    {
        unset($this->ingredients);
    }

    #[On('ingredient-created')]
    public function onIngredientCreated(int $id): void
    {
        $this->newIngredientIds[] = $id;
        unset($this->ingredients);
    }

    #[On('ingredient-updated')]
    public function onIngredientUpdated(int $id): void
    {
        if (!in_array($id, $this->newIngredientIds, true)) {
            $this->updatedIngredientIds[] = $id;
        }
        unset($this->ingredients);
    }

    #[On('ingredient-deleted')]
    public function onIngredientDeleted(): void
    {
        $this->newIngredientIds = [];
        $this->updatedIngredientIds = [];
        unset($this->ingredients);
    }

    public function render(): View
    {
        /** @var array<string, array{label: string, bg: string, icon: string}> $allergenConfig */
        $allergenConfig = config('restaurant.allergens');

        return view('livewire.dishes.ingredient-library', [
            'allergenConfig' => $allergenConfig,
        ]);
    }
}
