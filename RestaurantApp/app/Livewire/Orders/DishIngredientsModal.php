<?php

namespace App\Livewire\Orders;

use App\Models\Dish;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class DishIngredientsModal extends Component
{
    public ?int $dishId = null;

    public ?string $dishName = null;

    /** @var list<array{name: string, is_available: bool}> */
    public array $ingredients = [];

    #[On('open-dish-ingredients')]
    public function open(int $dishId): void
    {
        $dish = Dish::with('ingredients')->find($dishId);

        if (! $dish) {
            return;
        }

        $this->dishId = $dish->id;
        $this->dishName = $dish->name;
        $this->ingredients = $dish->ingredients
            ->map(fn ($ingredient): array => [
                'name' => $ingredient->name,
                'is_available' => (bool) $ingredient->is_available,
            ])
            ->values()
            ->all();

        $this->dispatch('open-modal', 'dish-ingredients');
    }

    public function render(): View
    {
        return view('livewire.orders.dish-ingredients-modal');
    }
}
