<?php

namespace App\Livewire\Orders;

use App\Enums\OrderStatus;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('New Bar Order')]
class BarOrderPage extends Component
{
    public string $search = '';

    public int $orderId;

    public function mount(): void
    {
        $existingOrder = Order::query()
            ->where('user_id', auth()->id())
            ->where('origin', 'bar')
            ->where('status', OrderStatus::Draft->value)
            ->whereNull('floor_plan_element_id')
            ->latest()
            ->first();

        if ($existingOrder) {
            $this->orderId = $existingOrder->id;

            return;
        }

        $order = Order::create([
            'floor_plan_element_id' => null,
            'reservation_id' => null,
            'user_id' => auth()->id(),
            'status' => OrderStatus::Draft,
            'origin' => 'bar',
        ]);

        $this->orderId = $order->id;
    }

    /**
     * @return array<int, array{name: string, price: float, qty: int, notes: string}>
     */
    #[Computed]
    public function initialCart(): array
    {
        $order = Order::find($this->orderId);
        if (! $order) {
            return [];
        }

        $cart = [];
        foreach ($order->items()->with('dish')->get() as $item) {
            $cart[$item->dish_id] = [
                'name' => $item->dish->name,
                'price' => (float) $item->unit_price,
                'qty' => $item->quantity,
                'notes' => $item->notes ?? '',
            ];
        }

        return $cart;
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
        return Dish::query()
            ->with('ingredients')
            ->where('is_available', true)
            ->where('is_bar_item', true)
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($q): void {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('name')
            ->get();
    }

    public function updatedSearch(): void
    {
        unset($this->dishes);
    }

    /**
     * @param  array<int, array{dish_id: int, qty: int, notes: string}>  $cartItems
     */
    public function placeOrder(array $cartItems, ?string $orderNotes): void
    {
        $this->authorize('Create Bar Order');

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
            'user_id' => auth()->id(),
        ]);

        $this->dispatch('toast', message: 'Bar order placed successfully!', type: 'success');

        $this->js("setTimeout(() => { window.location.href = '".route('orders')."'; }, 2000)");
    }

    public function cancelOrder(): void
    {
        $this->authorize('Create Bar Order');

        $order = Order::findOrFail($this->orderId);
        $order->update(['status' => OrderStatus::Cancelled]);

        $this->redirect(route('orders'));
    }

    public function render(): View
    {
        return view('livewire.orders.bar-order-page')
            ->layout('layouts.molveno');
    }
}
