<?php

namespace App\Livewire\Orders;

use App\Enums\OrderStatus;
use App\Models\Dish;
use App\Models\FloorPlanElement;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('New Order')]
class OrderPage extends Component
{
    public FloorPlanElement $table;

    public ?int $activeMenuId = null;

    public ?int $activeCategoryId = null;

    public string $search = '';

    /** @var list<string> */
    public array $dietaryFilters = [];

    /** @var list<string> */
    public array $allergenFilters = [];

    public int $orderId;

    public function mount(FloorPlanElement $floorPlanElement): void
    {
        $this->table = $floorPlanElement->load('floorPlan');

        $existingOrder = $floorPlanElement->orders()
            ->whereIn('status', [OrderStatus::Draft->value, OrderStatus::Active->value])
            ->latest()
            ->first();

        if ($existingOrder) {
            $this->orderId = $existingOrder->id;
        } else {
            $order = Order::create([
                'floor_plan_element_id' => $floorPlanElement->id,
                'status' => OrderStatus::Draft,
            ]);
            $this->orderId = $order->id;
        }

        $firstMenu = Menu::where('status', 'published')->first();
        if ($firstMenu) {
            $this->activeMenuId = $firstMenu->id;
        }
    }

    /**
     * @return Collection<int, Menu>
     */
    #[Computed]
    public function menus(): Collection
    {
        return Menu::where('status', 'published')->get();
    }

    /**
     * @return Collection<int, MenuCategory>
     */
    #[Computed]
    public function categories(): Collection
    {
        if (! $this->activeMenuId) {
            return new Collection;
        }

        return MenuCategory::where('menu_id', $this->activeMenuId)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @return array<string, array{label: string, bg: string, icon: string}>
     */
    #[Computed]
    public function allergenConfig(): array
    {
        return config('restaurant.allergens', []);
    }

    /**
     * @return Collection<int, Dish>
     */
    #[Computed]
    public function dishes(): Collection
    {
        if (! $this->activeMenuId) {
            return new Collection;
        }

        $dishes = Dish::query()
            ->with('ingredients')
            ->whereHas('menuCategories', function ($query): void {
                $query->where('menu_categories.menu_id', $this->activeMenuId);

                if ($this->activeCategoryId) {
                    $query->where('menu_categories.id', $this->activeCategoryId);
                }
            })
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($q): void {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->get();

        if (! empty($this->dietaryFilters)) {
            $dishes = $dishes->filter(function (Dish $dish): bool {
                foreach ($this->dietaryFilters as $flag) {
                    if (! in_array($flag, $dish->dietary)) {
                        return false;
                    }
                }

                return true;
            });
        }

        if (! empty($this->allergenFilters)) {
            $dishes = $dishes->filter(function (Dish $dish): bool {
                foreach ($this->allergenFilters as $allergen) {
                    if (in_array($allergen, $dish->allergens)) {
                        return false;
                    }
                }

                return true;
            });
        }

        return Collection::make($dishes->values());
    }

    public function selectMenu(int $menuId): void
    {
        $this->activeMenuId = $menuId;
        $this->activeCategoryId = null;
        $this->search = '';
        unset($this->categories, $this->dishes);

        $this->dispatch('active-menu-changed', menuId: $menuId, categoryId: null);
    }

    public function selectCategory(?int $categoryId): void
    {
        $this->activeCategoryId = $categoryId;
        $this->search = '';
        unset($this->dishes);
    }

    #[On('setMenuCategory')]
    public function setMenuCategory(int $menuId, ?int $categoryId = null): void
    {
        $this->activeMenuId = $menuId;
        $this->activeCategoryId = $categoryId;
        $this->search = '';
        unset($this->categories, $this->dishes);
    }

    public function updatedSearch(): void
    {
        unset($this->dishes);
    }

    public function toggleDietaryFilter(string $flag): void
    {
        if (in_array($flag, $this->dietaryFilters)) {
            $this->dietaryFilters = array_values(array_filter($this->dietaryFilters, fn ($f) => $f !== $flag));
        } else {
            $this->dietaryFilters[] = $flag;
        }

        unset($this->dishes);
    }

    public function toggleAllergenFilter(string $allergen): void
    {
        if (in_array($allergen, $this->allergenFilters)) {
            $this->allergenFilters = array_values(array_filter($this->allergenFilters, fn ($a) => $a !== $allergen));
        } else {
            $this->allergenFilters[] = $allergen;
        }

        unset($this->dishes);
    }

    /**
     * Receive the Alpine cart, persist all OrderItems, transition the order to active.
     *
     * @param array<int, array{dish_id: int, qty: int, notes: string}> $cartItems
     */
    public function placeOrder(array $cartItems, ?string $orderNotes): void
    {
        $this->authorize('Create Order');

        $order = Order::findOrFail($this->orderId);

        $order->items()->delete();

        $insertData = [];
        foreach ($cartItems as $item) {
            $dish = Dish::findOrFail((int) $item['dish_id']);
            $insertData[] = [
                'order_id' => $order->id,
                'dish_id' => $dish->id,
                'quantity' => max(1, (int) $item['qty']),
                'unit_price' => $dish->price,
                'notes' => $item['notes'] ?? null,
                'status' => 'pending',
                'course' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        OrderItem::insert($insertData);

        $order->update([
            'status' => OrderStatus::Active,
            'notes' => $orderNotes,
        ]);

        $this->dispatch('toast', message: 'Order placed successfully!', type: 'success');

        $this->js("setTimeout(() => { window.location.href = '" . route('tablemanagement') . "'; }, 2000)");
    }

    public function cancelOrder(): void
    {
        $this->authorize('Cancel Order');

        $order = Order::findOrFail($this->orderId);
        $order->update(['status' => OrderStatus::Cancelled]);

        $this->redirect(route('tablemanagement'));
    }

    public function render(): View
    {
        return view('livewire.orders.order-page')
            ->layout('layouts.molveno');
    }
}
