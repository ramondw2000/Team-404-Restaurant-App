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

    // Create/Edit form state
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $formName = '';

    /** @var list<string> */
    public array $formAllergens = [];

    /** @var list<string> */
    public array $formDietary = [];

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

    public function openCreateForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEditForm(int $id): void
    {
        $ingredient = Ingredient::findOrFail($id);
        $this->editingId = $id;
        $this->formName = $ingredient->name;
        $this->formAllergens = $ingredient->allergens ?? [];
        $this->formDietary = $ingredient->dietary ?? [];
        $this->showForm = true;
    }

    public function saveIngredient(): void
    {
        $rules = [
            'formName' => 'required|string|max:255|unique:ingredients,name'.($this->editingId ? ','.$this->editingId : ''),
        ];

        $this->validate($rules);

        $data = [
            'name' => $this->formName,
            'allergens' => $this->formAllergens,
            'dietary' => $this->formDietary,
        ];

        if ($this->editingId) {
            Ingredient::where('id', $this->editingId)->update($data);
        } else {
            Ingredient::create($data);
        }

        $this->resetForm();
        unset($this->ingredients);
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

    public function resetForm(): void
    {
        $this->showForm = false;
        $this->editingId = null;
        $this->formName = '';
        $this->formAllergens = [];
        $this->formDietary = [];
        $this->resetErrorBag();
    }

    #[On('dish-saved')]
    #[On('dish-deleted')]
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
