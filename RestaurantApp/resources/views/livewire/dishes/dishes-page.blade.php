<div class="min-h-screen bg-[#eaf4fa]">
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 py-8 flex flex-col gap-5">

        {{-- Page header --}}
        <x-ui.page-header title="Menu Management" subtitle="Molveno Lake Resort — Restaurant">
            <x-slot:actions>
                @if($activeView === 'dishes')
                    <x-ui.button wire:click="openDishSheet">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        Add Dish
                    </x-ui.button>
                @endif
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Main layout: sidebar + content --}}
        <div class="flex gap-6">
            {{-- Sidebar --}}
            <div class="w-64 shrink-0 hidden lg:block">
                <livewire:dishes.sidebar
                    :activeView="$activeView"
                    :activeMenuId="$activeMenuId"
                    :activeCategoryId="$activeCategoryId"
                />
            </div>

            {{-- Main content area --}}
            <div class="flex-1 min-w-0">
                @if($activeView === 'dishes')
                    <livewire:dishes.dish-grid />
                @elseif($activeView === 'ingredients')
                    <livewire:dishes.ingredient-library />
                @elseif($activeView === 'menu' && $activeMenuId)
                    <livewire:dishes.menu-view
                        :menuId="$activeMenuId"
                        :focusCategoryId="$activeCategoryId"
                        wire:key="menu-{{ $activeMenuId }}"
                    />
                @endif
            </div>
        </div>
    </div>

    {{-- Dish sheet overlay --}}
    @if($showDishSheet)
        <livewire:dishes.dish-sheet
            :dishId="$editingDishId"
            wire:key="dish-sheet-{{ $editingDishId ?? 'create' }}"
        />
    @endif
</div>
