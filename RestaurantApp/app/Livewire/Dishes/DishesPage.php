<?php

namespace App\Livewire\Dishes;

use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Menu Management')]
class DishesPage extends Component
{
    public string $activeView = 'dishes';

    public ?int $activeMenuId = null;

    public ?int $activeCategoryId = null;

    public ?int $editingDishId = null;

    public bool $showDishSheet = false;

    public ?int $editingIngredientId = null;

    public bool $showIngredientSheet = false;

    #[On('setView')]
    public function setView(string $view, ?int $menuId = null, ?int $categoryId = null): void
    {
        $this->activeView = $view;
        $this->activeMenuId = $menuId;
        $this->activeCategoryId = $categoryId;
    }

    #[On('open-dish-sheet')]
    public function openDishSheet(?int $dishId = null): void
    {
        $this->editingDishId = $dishId;
        $this->showDishSheet = true;
    }

    #[On('close-dish-sheet')]
    public function closeDishSheet(): void
    {
        $this->showDishSheet = false;
        $this->editingDishId = null;
    }

    #[On('open-ingredient-sheet')]
    public function openIngredientSheet(?int $ingredientId = null): void
    {
        $this->editingIngredientId = $ingredientId;
        $this->showIngredientSheet = true;
    }

    #[On('close-ingredient-sheet')]
    public function closeIngredientSheet(): void
    {
        $this->showIngredientSheet = false;
        $this->editingIngredientId = null;
    }

    #[On('dish-saved')]
    public function onDishSaved(): void
    {
        $this->closeDishSheet();
    }

    #[On('dish-deleted')]
    public function onDishDeleted(): void
    {
        $this->closeDishSheet();
    }

    #[On('menu-deleted')]
    public function onMenuDeleted(): void
    {
        $this->activeView = 'dishes';
        $this->activeMenuId = null;
        $this->activeCategoryId = null;
    }

    #[On('ingredient-updated')]
    #[On('ingredient-created')]
    #[On('ingredient-deleted')]
    public function onIngredientSaved(): void
    {
        $this->closeIngredientSheet();
    }

    public function render(): View
    {
        return view('livewire.dishes.dishes-page');
    }
}
