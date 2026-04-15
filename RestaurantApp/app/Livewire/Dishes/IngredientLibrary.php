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

        return $query->orderBy('name')->paginate(20);
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
    #[On('ingredient-updated')]
    #[On('ingredient-created')]
    #[On('ingredient-deleted')]
    public function refreshIngredients(): void
    {
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
