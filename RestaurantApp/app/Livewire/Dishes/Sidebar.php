<?php

namespace App\Livewire\Dishes;

use App\Models\Menu;
use App\Models\MenuCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Sidebar extends Component
{
    public string $activeView = 'dishes';

    public ?int $activeMenuId = null;

    public ?int $activeCategoryId = null;

    public bool $showNewMenuForm = false;

    public string $newMenuName = '';

    /** @var array<int, bool> */
    public array $expandedMenus = [];

    /**
     * @return Collection<int, Menu>
     */
    #[Computed]
    public function menus(): Collection
    {
        return Menu::with('categories')->get();
    }

    public function navigate(string $view, ?int $menuId = null, ?int $categoryId = null): void
    {
        $this->activeView = $view;
        $this->activeMenuId = $menuId;
        $this->activeCategoryId = $categoryId;
        $this->dispatch('setView', view: $view, menuId: $menuId, categoryId: $categoryId)->to(DishesPage::class);
    }

    public function toggleMenu(int $menuId): void
    {
        $this->expandedMenus[$menuId] = ! ($this->expandedMenus[$menuId] ?? false);
    }

    public function createMenu(): void
    {
        $this->validate(['newMenuName' => 'required|string|max:255']);

        $menu = Menu::create(['name' => $this->newMenuName]);
        $menu->seedDefaultCategories();

        $this->newMenuName = '';
        $this->showNewMenuForm = false;
        unset($this->menus);
        $this->navigate('menu', $menu->id);
    }

    public function reorderCategory(int $id, int $position): void
    {
        $category = MenuCategory::findOrFail($id);

        // Get all sibling categories in current order
        $siblings = MenuCategory::where('menu_id', $category->menu_id)
            ->orderBy('sort_order')
            ->pluck('id')
            ->all();

        // Remove the moved item and re-insert at new position
        $siblings = array_values(array_diff($siblings, [$id]));
        array_splice($siblings, $position, 0, [$id]);

        // Update all sort orders
        foreach ($siblings as $index => $categoryId) {
            MenuCategory::where('id', $categoryId)->update(['sort_order' => $index]);
        }

        unset($this->menus);
    }

    #[On('menu-updated')]
    #[On('dish-saved')]
    #[On('dish-deleted')]
    #[On('menu-deleted')]
    public function refreshMenus(): void
    {
        unset($this->menus);
    }

    public function render(): View
    {
        return view('livewire.dishes.sidebar');
    }
}
