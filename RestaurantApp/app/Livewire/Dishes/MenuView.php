<?php

namespace App\Livewire\Dishes;

use App\Models\Dish;
use App\Models\Menu;
use App\Models\MenuCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class MenuView extends Component
{
    public int $menuId;

    public ?int $focusCategoryId = null;

    // Edit menu form
    public bool $editingMenu = false;

    public string $menuName = '';

    public ?string $menuDescription = null;

    public string $menuStatus = 'draft';

    // Add category form
    public bool $showAddCategory = false;

    public string $newCategoryName = '';

    // Add dishes to category
    public ?int $addingDishesToCategoryId = null;

    public string $dishSearch = '';

    // Rename category
    public ?int $renamingCategoryId = null;

    public string $renameCategoryName = '';

    public function mount(int $menuId, ?int $focusCategoryId = null): void
    {
        $this->menuId = $menuId;
        $this->focusCategoryId = $focusCategoryId;
        $this->loadMenuData();
    }

    private function loadMenuData(): void
    {
        $menu = Menu::findOrFail($this->menuId);
        $this->menuName = $menu->name;
        $this->menuDescription = $menu->description;
        $this->menuStatus = $menu->status;
    }

    #[Computed]
    public function menu(): Menu
    {
        return Menu::with(['categories.dishes.ingredients'])->findOrFail($this->menuId);
    }

    #[Computed]
    public function availableDishes(): Collection
    {
        $query = Dish::query();

        if ($this->dishSearch !== '') {
            $query->where('name', 'like', '%'.$this->dishSearch.'%');
        }

        if ($this->addingDishesToCategoryId) {
            $existingIds = MenuCategory::find($this->addingDishesToCategoryId)
                ?->dishes()
                ->pluck('dishes.id')
                ->all() ?? [];

            if ($existingIds !== []) {
                $query->whereNotIn('id', $existingIds);
            }
        }

        return $query->orderBy('name')->limit(15)->get();
    }

    // Menu management
    public function saveMenu(): void
    {
        $this->validate([
            'menuName' => 'required|string|max:255',
            'menuDescription' => 'nullable|string',
            'menuStatus' => 'required|in:draft,published',
        ]);

        Menu::where('id', $this->menuId)->update([
            'name' => $this->menuName,
            'description' => $this->menuDescription,
            'status' => $this->menuStatus,
        ]);

        $this->editingMenu = false;
        unset($this->menu);
        $this->dispatch('menu-updated');
    }

    public function deleteMenu(): void
    {
        Menu::destroy($this->menuId);
        $this->dispatch('menu-deleted');
        $this->skipRender();
    }

    public function toggleStatus(): void
    {
        $this->menuStatus = $this->menuStatus === 'published' ? 'draft' : 'published';
        Menu::where('id', $this->menuId)->update(['status' => $this->menuStatus]);
        unset($this->menu);
        $this->dispatch('menu-updated');
    }

    // Category management
    public function addCategory(): void
    {
        $this->validate(['newCategoryName' => 'required|string|max:255']);

        $maxSort = MenuCategory::where('menu_id', $this->menuId)->max('sort_order') ?? -1;

        MenuCategory::create([
            'menu_id' => $this->menuId,
            'name' => $this->newCategoryName,
            'sort_order' => $maxSort + 1,
        ]);

        $this->newCategoryName = '';
        $this->showAddCategory = false;
        unset($this->menu);
        $this->dispatch('menu-updated');
    }

    public function startRenameCategory(int $categoryId): void
    {
        $category = MenuCategory::findOrFail($categoryId);
        $this->renamingCategoryId = $categoryId;
        $this->renameCategoryName = $category->name;
    }

    public function renameCategory(): void
    {
        $this->validate(['renameCategoryName' => 'required|string|max:255']);

        MenuCategory::where('id', $this->renamingCategoryId)->update(['name' => $this->renameCategoryName]);

        $this->renamingCategoryId = null;
        $this->renameCategoryName = '';
        unset($this->menu);
        $this->dispatch('menu-updated');
    }

    public function deleteCategory(int $categoryId): void
    {
        MenuCategory::destroy($categoryId);
        unset($this->menu);
        $this->dispatch('menu-updated');
    }

    public function reorderDish(int $id, int $position): void
    {
        // Find which category this dish belongs to in this menu
        $pivot = DB::table('menu_category_dish')
            ->join('menu_categories', 'menu_categories.id', '=', 'menu_category_dish.menu_category_id')
            ->where('menu_categories.menu_id', $this->menuId)
            ->where('menu_category_dish.dish_id', $id)
            ->select('menu_category_dish.menu_category_id')
            ->first();

        if (! $pivot) {
            return;
        }

        $categoryId = $pivot->menu_category_id;
        $category = MenuCategory::findOrFail($categoryId);

        // Get current dish order in this category
        $dishIds = $category->dishes()->orderByPivot('sort_order')->pluck('dishes.id')->all();
        $dishIds = array_values(array_diff($dishIds, [$id]));
        array_splice($dishIds, $position, 0, [$id]);

        foreach ($dishIds as $index => $dishId) {
            $category->dishes()->updateExistingPivot($dishId, ['sort_order' => $index]);
        }

        unset($this->menu);
    }

    // Dish assignment
    public function openAddDishes(int $categoryId): void
    {
        $this->addingDishesToCategoryId = $categoryId;
        $this->dishSearch = '';
        unset($this->availableDishes);
    }

    public function closeAddDishes(): void
    {
        $this->addingDishesToCategoryId = null;
        $this->dishSearch = '';
    }

    public function assignDish(int $dishId): void
    {
        if (! $this->addingDishesToCategoryId) {
            return;
        }

        $category = MenuCategory::findOrFail($this->addingDishesToCategoryId);
        $maxSort = $category->dishes()->max('menu_category_dish.sort_order') ?? -1;
        $category->dishes()->attach($dishId, ['sort_order' => $maxSort + 1]);

        unset($this->menu, $this->availableDishes);
    }

    public function removeDish(int $categoryId, int $dishId): void
    {
        MenuCategory::findOrFail($categoryId)->dishes()->detach($dishId);
        unset($this->menu);
    }

    #[On('dish-saved')]
    #[On('dish-deleted')]
    public function refreshMenu(): void
    {
        unset($this->menu, $this->availableDishes);
    }

    public function render(): View
    {
        /** @var array<string, array{label: string, bg: string, icon: string}> $allergenConfig */
        $allergenConfig = config('restaurant.allergens');

        return view('livewire.dishes.menu-view', [
            'allergenConfig' => $allergenConfig,
        ]);
    }
}
