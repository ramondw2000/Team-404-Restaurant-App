<?php

namespace App\Livewire\Orders;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class Sidebar extends Component
{
    #[Reactive]
    public ?int $activeMenuId = null;

    #[Reactive]
    public ?int $activeCategoryId = null;

    #[Reactive]
    public bool $barMode = false;

    public int $floorPlanId;

    public string $targetComponent = OrderPage::class;

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
        $this->dispatch('setMenuCategory', menuId: $menuId, categoryId: null)->to($this->targetComponent);
    }

    public function selectCategory(int $menuId, int $categoryId): void
    {
        $this->dispatch('setMenuCategory', menuId: $menuId, categoryId: $categoryId)->to($this->targetComponent);
    }

    public function selectBar(): void
    {
        $this->dispatch('setBarMode')->to($this->targetComponent);
    }

    public function toggleMenu(int $menuId): void
    {
        $this->expandedMenus[$menuId] = ! ($this->expandedMenus[$menuId] ?? false);
    }

    public function render(): View
    {
        return view('livewire.orders.sidebar');
    }
}
