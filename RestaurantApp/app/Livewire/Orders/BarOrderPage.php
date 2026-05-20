<?php

namespace App\Livewire\Orders;

use App\Enums\OrderStatus;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('New Bar Order')]
class BarOrderPage extends Component
{
    public string $search = '';

    public int $orderId;

    public string $guestType = 'walk_in';

    public string $roomNumber = '';

    public bool $showTicketModal = false;

    public string $ticketMode = 'display';

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
            $this->guestType = $existingOrder->guest_type ?? 'walk_in';
            $this->roomNumber = $existingOrder->room_number ?? '';

            return;
        }

        $order = Order::create([
            'floor_plan_element_id' => null,
            'reservation_id' => null,
            'user_id' => auth()->id(),
            'status' => OrderStatus::Draft,
            'origin' => 'bar',
            'guest_type' => 'walk_in',
        ]);

        $this->orderId = $order->id;
    }

    public function updatedGuestType(): void
    {
        $order = Order::find($this->orderId);
        if ($order) {
            $order->update(['guest_type' => $this->guestType === 'hotel' ? 'hotel' : 'walk_in']);
        }
    }

    public function updatedRoomNumber(): void
    {
        $order = Order::find($this->orderId);
        if ($order) {
            $order->update(['room_number' => $this->roomNumber ?: null]);
        }
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
                $search = '%'.addcslashes($this->search, '%_\\').'%';
                $query->where(function ($q) use ($search): void {
                    $q->whereRaw('name LIKE ?', [$search])
                        ->orWhereRaw('description LIKE ?', [$search]);
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
            $dish = Dish::with('ingredients')->findOrFail((int) $item['dish_id']);

            if ($dish->is_out_of_stock) {
                throw ValidationException::withMessages([
                    'cart' => "\"{$dish->name}\" is out of stock and cannot be ordered.",
                ]);
            }

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
            'guest_type' => $this->guestType === 'hotel' ? 'hotel' : 'walk_in',
            'room_number' => $this->roomNumber ?: null,
        ]);

        $this->dispatch('toast', message: 'Bar order placed successfully!', type: 'success');

        $this->js("setTimeout(() => { window.location.href = '".route('orders')."'; }, 2000)");
    }

    public function openTicket(string $mode): void
    {
        $this->ticketMode = $mode;
        $this->showTicketModal = true;
    }

    public function closeTicket(): void
    {
        $this->showTicketModal = false;
    }

    public function markAsPaid(): void
    {
        $this->authorize('Create Bar Order');

        $order = Order::findOrFail($this->orderId);
        $order->update(['paid' => true]);

        $this->dispatch('toast', message: 'Order marked as paid!', type: 'success');
    }

    /**
     * @return array<string, mixed>|null
     */
    #[Computed]
    public function ticketData(): ?array
    {
        $order = Order::with(['items.dish', 'user'])->find($this->orderId);
        if (! $order) {
            return null;
        }

        $items = $order->items->map(fn ($item): array => [
            'name' => $item->dish?->name ?? 'Unknown',
            'qty' => $item->quantity,
            'notes' => $item->notes,
        ])->all();

        return [
            'order_id' => $order->id,
            'guest_type' => $order->guest_type ?? 'walk_in',
            'room_number' => $order->room_number,
            'items' => $items,
            'total' => $order->items->sum(fn ($item) => $item->quantity * $item->unit_price),
            'created_at' => $order->created_at?->format('H:i'),
            'waiter' => $order->user?->name ?? '—',
        ];
    }

    public function render(): View
    {
        return view('livewire.orders.bar-order-page')
            ->layout('layouts.molveno');
    }
}
