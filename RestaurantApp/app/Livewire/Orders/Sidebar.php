<?php

namespace App\Livewire\Orders;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Sidebar extends Component
{
    public ?int $activeMenuId = null;

    public ?int $activeCategoryId = null;

    public int $floorPlanId;

    /** @var array<int, bool> */
    public array $expandedMenus = [];

    /**
     * @return Collection<int, Menu>
     */
    #[Computed]
    public function menus(): Collection
    {
        return Menu::with('categories')
            ->where('status', 'published')
            ->get();
    }

    public function selectMenu(int $menuId): void
    {
        $this->activeMenuId = $menuId;
        $this->activeCategoryId = null;
        $this->dispatch('setMenuCategory', menuId: $menuId, categoryId: null)->to(OrderPage::class);
    }

    public function selectCategory(int $menuId, int $categoryId): void
    {
        $this->activeMenuId = $menuId;
        $this->activeCategoryId = $categoryId;
        $this->dispatch('setMenuCategory', menuId: $menuId, categoryId: $categoryId)->to(OrderPage::class);
    }

    public function toggleMenu(int $menuId): void
    {
        $this->expandedMenus[$menuId] = ! ($this->expandedMenus[$menuId] ?? false);
    }

    #[On('active-menu-changed')]
    public function syncActiveMenu(int $menuId, ?int $categoryId = null): void
    {
        $this->activeMenuId = $menuId;
        $this->activeCategoryId = $categoryId;
    }

    public function render(): View
    {
        return view('livewire.orders.sidebar');
    }
}
