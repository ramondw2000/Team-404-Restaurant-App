<div class="min-h-screen bg-[#eaf4fa]" x-data="{ sidebarOpen: false }">
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 py-8 flex flex-col gap-5">

        {{-- Page header --}}
        <x-ui.page-header title="Menu Management" subtitle="Molveno Lake Resort — Restaurant">
            <x-slot:actions>
                {{-- Mobile sidebar toggle --}}
                <button
                    type="button"
                    class="lg:hidden flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors"
                    @click="sidebarOpen = true"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    Menus
                </button>

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

            {{-- Mobile sidebar overlay --}}
            <div
                x-show="sidebarOpen"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-40 bg-black/40 lg:hidden"
                x-cloak
                @click="sidebarOpen = false"
            ></div>

            <div
                x-show="sidebarOpen"
                x-transition:enter="transition ease-[cubic-bezier(0.32,0.72,0,1)] duration-350"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-[cubic-bezier(0.32,0.72,0,1)] duration-350"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="fixed top-0 left-0 z-50 w-72 h-dvh overflow-y-auto bg-white shadow-2xl lg:hidden"
                x-cloak
                @click.stop
            >
                <div class="flex items-center justify-between px-4 pt-4 pb-3 border-b border-gray-100">
                    <span class="text-sm font-bold text-gray-800">Menu Management</span>
                    <button type="button" @click="sidebarOpen = false" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <livewire:dishes.sidebar
                    :activeView="$activeView"
                    :activeMenuId="$activeMenuId"
                    :activeCategoryId="$activeCategoryId"
                    wire:key="dishes-sidebar-mobile"
                />
            </div>

            {{-- Desktop sidebar --}}
            <div class="w-64 shrink-0 hidden lg:block">
                <livewire:dishes.sidebar
                    :activeView="$activeView"
                    :activeMenuId="$activeMenuId"
                    :activeCategoryId="$activeCategoryId"
                    wire:key="dishes-sidebar-desktop"
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

    {{-- Ingredient sheet overlay --}}
    @if($showIngredientSheet)
        <livewire:dishes.ingredient-sheet
            :ingredientId="$editingIngredientId"
            wire:key="ingredient-sheet-{{ $editingIngredientId ?? 'create' }}"
        />
    @endif
</div>
