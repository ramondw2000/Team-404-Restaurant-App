<?php

namespace App\Livewire\Dishes;

use App\Models\Dish;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class DishGrid extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    /** @var list<string> */
    public array $dietaryFilters = [];

    /** @var list<string> */
    public array $freeFromFilters = [];

    public string $sortBy = 'name';

    public string $sortDir = 'asc';

    public int $perPage = 20;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDietaryFilters(): void
    {
        $this->resetPage();
    }

    public function updatedFreeFromFilters(): void
    {
        $this->resetPage();
    }

    public function toggleDietary(string $value): void
    {
        if (in_array($value, $this->dietaryFilters)) {
            $this->dietaryFilters = array_values(array_diff($this->dietaryFilters, [$value]));
        } else {
            $this->dietaryFilters[] = $value;
        }
        $this->resetPage();
    }

    public function toggleFreeFrom(string $value): void
    {
        if (in_array($value, $this->freeFromFilters)) {
            $this->freeFromFilters = array_values(array_diff($this->freeFromFilters, [$value]));
        } else {
            $this->freeFromFilters[] = $value;
        }
        $this->resetPage();
    }

    public function setSort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDir = 'asc';
        }
        $this->resetPage();
    }

    public function setPerPage(int $size): void
    {
        $this->perPage = in_array($size, [12, 20, 40]) ? $size : 20;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->dietaryFilters = [];
        $this->freeFromFilters = [];
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, Dish>
     */
    #[Computed]
    public function dishes(): LengthAwarePaginator
    {
        $query = Dish::with('ingredients');

        if ($this->search !== '') {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        // Allergen/dietary filtering requires loading ingredients
        // We apply these as post-query filters via a subquery approach
        if ($this->freeFromFilters !== []) {
            foreach ($this->freeFromFilters as $allergen) {
                $query->whereDoesntHave('ingredients', function ($q) use ($allergen): void {
                    $q->whereJsonContains('allergens', $allergen);
                });
            }
        }

        if ($this->dietaryFilters !== []) {
            foreach ($this->dietaryFilters as $dietary) {
                if ($dietary === 'vegan') {
                    // All ingredients must be vegan
                    $query->whereDoesntHave('ingredients', function ($q): void {
                        $q->whereJsonDoesntContain('dietary', 'vegan');
                    })->has('ingredients');
                } elseif ($dietary === 'vegetarian') {
                    // All ingredients must be vegetarian or vegan
                    $query->whereDoesntHave('ingredients', function ($q): void {
                        $q->whereJsonDoesntContain('dietary', 'vegetarian')
                            ->whereJsonDoesntContain('dietary', 'vegan');
                    })->has('ingredients');
                }
            }
        }

        $query->orderBy($this->sortBy, $this->sortDir);

        return $query->paginate($this->perPage);
    }

    #[On('dish-saved')]
    #[On('dish-deleted')]
    public function refreshDishes(): void
    {
        unset($this->dishes);
    }

    public function render(): View
    {
        /** @var array<string, array{label: string, bg: string, icon: string}> $allergenConfig */
        $allergenConfig = config('restaurant.allergens');

        return view('livewire.dishes.dish-grid', [
            'allergenConfig' => $allergenConfig,
        ]);
    }
}
