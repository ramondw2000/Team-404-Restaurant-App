<?php

namespace App\Livewire\Dishes;

use App\Models\Ingredient;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

class IngredientSheet extends Component
{
    public ?int $ingredientId = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    /** @var list<string> */
    public array $allergens = [];

    /** @var list<string> */
    public array $dietary = [];

    public function mount(?int $ingredientId = null): void
    {
        $this->ingredientId = $ingredientId;

        if ($ingredientId) {
            $ingredient = Ingredient::findOrFail($ingredientId);
            $this->name = $ingredient->name;
            $this->allergens = $ingredient->allergens ?? [];
            $this->dietary = $ingredient->dietary ?? [];
        }
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:255|unique:ingredients,name'.($this->ingredientId ? ','.$this->ingredientId : ''),
        ];

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'allergens' => $this->allergens,
            'dietary' => $this->dietary,
        ];

        if ($this->ingredientId) {
            Ingredient::where('id', $this->ingredientId)->update($data);
            $this->dispatch('ingredient-updated', id: $this->ingredientId);
        } else {
            $ingredient = Ingredient::create($data);
            $this->dispatch('ingredient-created', id: $ingredient->id);
        }

        $this->dispatch('close-ingredient-sheet');
    }

    public function deleteIngredient(): void
    {
        if (! $this->ingredientId) {
            return;
        }

        $ingredient = Ingredient::withCount('dishes')->findOrFail($this->ingredientId);

        if ($ingredient->dishes_count > 0) {
            $this->addError('delete', "Cannot delete \"{$ingredient->name}\" — used by {$ingredient->dishes_count} dish(es). Remove from all dishes first.");

            return;
        }

        $ingredient->delete();
        $this->dispatch('ingredient-deleted');
        $this->dispatch('close-ingredient-sheet');
    }

    public function close(): void
    {
        $this->dispatch('close-ingredient-sheet');
    }

    public function render(): View
    {
        /** @var array<string, array{label: string, bg: string, icon: string}> $allergenConfig */
        $allergenConfig = config('restaurant.allergens');

        return view('livewire.dishes.ingredient-sheet', [
            'allergenConfig' => $allergenConfig,
        ]);
    }
}
