<x-ordermanagement.styles />

<div
    class="min-h-screen bg-[#eaf4fa]"
    x-data="orderCart()"
    x-init="init()"
>
    {{-- ══════════════════════════════════════════════════════════
         Layout: sidebar + main
    ══════════════════════════════════════════════════════════ --}}
    <div class="flex h-screen overflow-hidden">

        {{-- ── Fixed left sidebar (md+) ── --}}
        <aside
            class="hidden md:flex flex-col w-64 shrink-0 bg-white border-r border-gray-200 shadow-sm h-full"
        >
            <div class="shrink-0 px-4 pt-5 pb-3 border-b border-gray-100">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Order for</p>
                <p class="text-base font-bold text-gray-900 mt-0.5">{{ $table->table_name }}</p>
                <p class="text-xs text-gray-500">{{ $table->floorPlan?->name }}</p>
            </div>

            <div class="flex-1 overflow-hidden">
                <livewire:orders.sidebar
                    :activeMenuId="$activeMenuId"
                    :activeCategoryId="$activeCategoryId"
                    :floorPlanId="$table->floor_plan_id"
                    wire:key="order-sidebar-{{ $table->id }}"
                />
            </div>
        </aside>

        {{-- ── Mobile sidebar overlay ── --}}
        <div
            x-show="sidebarOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 bg-black/40 md:hidden"
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
            class="fixed top-0 left-0 z-50 w-72 h-dvh bg-white shadow-2xl flex flex-col md:hidden"
            x-cloak
            @click.stop
        >
            <div class="shrink-0 flex items-center justify-between px-4 pt-4 pb-3 border-b border-gray-100">
                <div>
                    <p class="text-sm font-bold text-gray-900">{{ $table->table_name }}</p>
                    <p class="text-xs text-gray-500">{{ $table->floorPlan?->name }}</p>
                </div>
                <button @click="sidebarOpen = false" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors" type="button">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="flex-1 overflow-hidden">
                <livewire:orders.sidebar
                    :activeMenuId="$activeMenuId"
                    :activeCategoryId="$activeCategoryId"
                    :floorPlanId="$table->floor_plan_id"
                    wire:key="order-sidebar-mobile-{{ $table->id }}"
                />
            </div>
        </div>

        {{-- ── Main content ── --}}
        <div class="flex-1 flex flex-col overflow-hidden">

            {{-- Scrollable main area --}}
            <div class="flex-1 overflow-y-auto pb-24">
                <div class="max-w-screen-xl mx-auto px-4 sm:px-6 py-6 flex flex-col gap-5">

                    {{-- Page header --}}
                    <x-ui.page-header
                        title="New Order — {{ $table->table_name }}"
                        subtitle="{{ $table->floorPlan?->name }}"
                    >
                        <x-slot:actions>
                            {{-- Mobile sidebar toggle --}}
                            <button
                                type="button"
                                class="md:hidden flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors"
                                @click="sidebarOpen = true"
                            >
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                                Menu
                            </button>

                            @can('Cancel Order')
                                <x-ui.button variant="danger" wire:click="cancelOrder" wire:confirm="Cancel this order? The table will be returned to Available.">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                    Cancel Order
                                </x-ui.button>
                            @endcan
                        </x-slot:actions>
                    </x-ui.page-header>

                    @if(!$activeMenuId)
                        {{-- No menu selected --}}
                        <x-ui.empty-state
                            icon="book-open"
                            title="Select a menu"
                            description="Choose a menu from the sidebar to start adding dishes."
                        />
                    @else
                        {{-- Filters card: search + category tabs + dietary/allergen pills --}}
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex flex-col gap-3">

                            {{-- Search --}}
                            <x-ui.search-input
                                wire:model.live.debounce.300ms="search"
                                placeholder="Search dishes…"
                            />

                            {{-- Category tabs --}}
                            @if($this->categories->isNotEmpty())
                                <x-ui.tab-group class="overflow-x-auto scrollbar-hide pb-0.5">
                                    <x-ui.tab
                                        :active="$activeCategoryId === null"
                                        wire:click="selectCategory(null)"
                                    >
                                        All
                                    </x-ui.tab>
                                    @foreach($this->categories as $category)
                                        <x-ui.tab
                                            wire:key="cat-tab-{{ $category->id }}"
                                            :active="$activeCategoryId === $category->id"
                                            wire:click="selectCategory({{ $category->id }})"
                                        >
                                            {{ $category->name }}
                                        </x-ui.tab>
                                    @endforeach
                                </x-ui.tab-group>
                            @endif

                            {{-- Dietary + free-from filter pills --}}
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                                <span class="text-xs font-semibold text-gray-500 shrink-0">Dietary:</span>

                                <button
                                    type="button"
                                    wire:click="toggleDietaryFilter('vegetarian')"
                                    class="filter-btn inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium border transition-colors {{ in_array('vegetarian', $dietaryFilters) ? 'filter-active' : 'bg-white border-gray-200 text-gray-700' }}"
                                >
                                    <x-dishes.dietary-icon type="vegetarian" size="sm" />
                                    Vegetarian
                                </button>

                                <button
                                    type="button"
                                    wire:click="toggleDietaryFilter('vegan')"
                                    class="filter-btn inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium border transition-colors {{ in_array('vegan', $dietaryFilters) ? 'filter-active' : 'bg-white border-gray-200 text-gray-700' }}"
                                >
                                    <x-dishes.dietary-icon type="vegan" size="sm" />
                                    Vegan
                                </button>

                                <span class="text-gray-300 hidden sm:inline">|</span>
                                <span class="text-xs font-semibold text-gray-500 shrink-0">Free from:</span>

                                @foreach($this->allergenConfig as $allergenKey => $cfg)
                                    <button
                                        type="button"
                                        wire:key="allergen-pill-{{ $allergenKey }}"
                                        wire:click="toggleAllergenFilter('{{ $allergenKey }}')"
                                        class="filter-btn inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium border transition-colors {{ in_array($allergenKey, $allergenFilters) ? 'filter-active' : 'bg-white border-gray-200 text-gray-700' }}"
                                    >
                                        <x-dishes.allergen-icon :bg="$cfg['bg']" :icon="$cfg['icon']" size="sm" />
                                        {{ $cfg['label'] }}-free
                                    </button>
                                @endforeach
                            </div>

                        </div>

                        {{-- Dish grid --}}
                        @if($this->dishes->isEmpty())
                            <x-ui.empty-state
                                icon="search"
                                title="No dishes found"
                                description="Try a different search term or category."
                            />
                        @else
                            <x-ordermanagement.dish-grid>
                                @foreach($this->dishes as $dish)
                                    <div
                                        wire:key="dish-{{ $dish->id }}"
                                        class="dish-card"
                                        id="dish-card-{{ $dish->id }}"
                                    >
                                        {{-- Text side --}}
                                        <div class="dish-card-body">
                                            {{-- Name + dietary icons --}}
                                            <div class="flex items-start gap-2 flex-wrap">
                                                <span class="text-sm font-bold text-gray-900 leading-snug">{{ $dish->name }}</span>
                                                <div class="flex items-center gap-1 mt-0.5">
                                                    @if(in_array('vegetarian', $dish->dietary))
                                                        <div title="Vegetarian" class="w-4 h-4 rounded-full bg-green-500 flex items-center justify-center shrink-0">
                                                            <svg viewBox="0 0 16 16" width="8" height="8"><path fill="black" d="M3 14c0-5 4-11 10-12C13 7 11 11 8 13l4-3c-1 3-5 5-9 4z"/></svg>
                                                        </div>
                                                    @endif
                                                    @if(in_array('vegan', $dish->dietary))
                                                        <div title="Vegan" class="w-4 h-4 rounded-full bg-green-700 flex items-center justify-center shrink-0">
                                                            <svg viewBox="0 0 16 16" width="8" height="8"><path stroke="black" stroke-width="1.5" fill="none" stroke-linecap="round" d="M8 14V8M8 8C8 5 5 2 2 2C2 5 5 8 8 8M8 8C8 5 11 2 14 2C14 5 11 8 8 8"/></svg>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Price --}}
                                            <p class="text-sm font-semibold text-primary">&euro;&nbsp;{{ number_format((float) $dish->price, 2) }}</p>

                                            {{-- Description --}}
                                            @if($dish->description)
                                                <p class="text-xs text-gray-500 leading-snug">{{ $dish->description }}</p>
                                            @endif

                                            {{-- Allergen icons --}}
                                            @if(!empty($dish->allergens))
                                                <div class="flex items-center gap-1 flex-wrap mt-1">
                                                    @foreach($dish->allergens as $allergenKey)
                                                        @if(isset($this->allergenConfig[$allergenKey]))
                                                            <x-dishes.allergen-icon
                                                                :bg="$this->allergenConfig[$allergenKey]['bg']"
                                                                :icon="$this->allergenConfig[$allergenKey]['icon']"
                                                                :title="$this->allergenConfig[$allergenKey]['label']"
                                                                shadow />
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Image side --}}
                                        <div class="dish-card-image">
                                            @if($dish->photo_path)
                                                <img src="{{ Storage::url($dish->photo_path) }}" alt="{{ $dish->name }}">
                                            @else
                                                <svg class="text-gray-300" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round">
                                                    <circle cx="12" cy="13" r="8"/><path d="M7 5v3M8 5v3M7.5 8v5"/>
                                                    <path d="M15 5c1 1 1.5 2 1.5 3v6"/><path d="M15 5c-1 1-1.5 2-1.5 3h3"/>
                                                </svg>
                                            @endif

                                            {{-- Qty badge --}}
                                            <div
                                                class="qty-badge"
                                                x-bind:class="(cart[{{ $dish->id }}]?.qty ?? 0) > 0 ? 'visible' : ''"
                                                x-text="cart[{{ $dish->id }}]?.qty ?? 0"
                                            ></div>

                                            {{-- Add button --}}
                                            <button
                                                type="button"
                                                class="btn-add-dish"
                                                @click="openAddModal({{ $dish->id }}, {{ json_encode($dish->name) }}, {{ (float) $dish->price }}, {{ json_encode($dish->allergens) }}, {{ json_encode($dish->dietary) }})"
                                                aria-label="Add {{ $dish->name }}"
                                            >
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </x-ordermanagement.dish-grid>
                        @endif
                    @endif

                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         Add-dish modal (Alpine)
    ══════════════════════════════════════════════════════════ --}}
    <div
        x-show="addModal.open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
        x-cloak
        @click.self="closeAddModal()"
        @keydown.escape.window="closeAddModal()"
    >
        <div
            x-show="addModal.open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="w-full max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col"
            @click.stop
        >
            <div class="px-5 py-4 border-b border-gray-100 flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="text-base font-bold text-gray-900 leading-snug" x-text="addModal.name"></h2>
                    <p class="text-sm font-semibold text-molveno-blue-600 mt-0.5">&euro;&nbsp;<span x-text="addModal.price.toFixed(2)"></span></p>
                </div>
                <button @click="closeAddModal()" class="w-8 h-8 shrink-0 flex items-center justify-center rounded-full text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors" type="button">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="px-5 py-4 flex flex-col gap-4">
                {{-- Qty --}}
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-gray-700">Quantity</span>
                    <div class="flex items-center gap-2 ms-auto">
                        <button type="button" class="w-7 h-7 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition-colors" @click="addModal.qty = Math.max(1, addModal.qty - 1)">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M5 12h14"/></svg>
                        </button>
                        <span class="text-sm font-bold text-gray-800 w-6 text-center" x-text="addModal.qty"></span>
                        <button type="button" class="w-7 h-7 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition-colors" @click="addModal.qty++">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Notes for kitchen
                        <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <textarea
                        x-model="addModal.notes"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl resize-none focus:outline-none focus:ring-2 focus:ring-molveno-blue-400 focus:border-transparent"
                        rows="2"
                        placeholder="e.g. No onions, extra sauce on the side…"
                    ></textarea>
                </div>
            </div>

            <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
                <x-ui.button variant="secondary" class="flex-1 justify-center" x-on:click="closeAddModal()">Cancel</x-ui.button>
                <x-ui.button class="flex-1 justify-center" x-on:click="confirmAdd()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                    Add to Order
                </x-ui.button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         Fixed order bar (Alpine)
    ══════════════════════════════════════════════════════════ --}}
    <div
        x-show="itemCount > 0"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-full opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-full opacity-0"
        class="fixed bottom-0 left-0 right-0 z-30 bg-white border-t border-gray-200 shadow-2xl px-4 sm:px-6 py-3"
        x-cloak
    >
        <div class="max-w-screen-xl mx-auto flex items-center justify-between gap-4">
            <div class="flex items-center gap-4 min-w-0">
                <div class="w-9 h-9 rounded-full bg-molveno-blue-500 flex items-center justify-center shrink-0 shadow-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-gray-800 truncate">
                        <span x-text="itemCount"></span> <span x-text="itemCount === 1 ? 'item' : 'items'"></span>
                        <span class="text-gray-400 font-normal mx-1">&middot;</span>
                        <span class="text-gray-500 font-normal">{{ $table->table_name }}</span>
                    </p>
                    <p class="text-xs text-gray-400">&euro;&nbsp;<span x-text="orderTotal.toFixed(2)" class="font-semibold text-gray-600"></span></p>
                </div>
            </div>
            <x-ui.button class="shrink-0" x-on:click="openReview()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                Review Order
            </x-ui.button>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         Review screen (Alpine full-screen overlay)
    ══════════════════════════════════════════════════════════ --}}
    <div
        x-show="reviewOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-[#eaf4fa] flex flex-col"
        style="height: 100dvh;"
        x-cloak
    >
        {{-- Header --}}
        <div class="shrink-0 bg-white border-b border-gray-200 shadow-sm">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between gap-4">
                <x-ui.button variant="ghost" class="text-molveno-blue-500 hover:text-molveno-blue-700" x-on:click="reviewOpen = false">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                    Back
                </x-ui.button>
                <h2 class="text-base font-bold text-gray-900 absolute left-1/2 -translate-x-1/2">Your Order</h2>
                <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full">{{ $table->table_name }}</span>
            </div>
        </div>

        {{-- Scrollable content --}}
        <div class="flex-1 overflow-y-auto">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 py-6 flex flex-col gap-4">

                {{-- Order notes --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Order notes <span class="text-gray-400 font-normal">(optional)</span></label>
                    <textarea
                        x-model="orderNotes"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl resize-none bg-white focus:outline-none focus:ring-2 focus:ring-molveno-blue-400 focus:border-transparent"
                        rows="2"
                        placeholder="Special instructions for the whole order…"
                    ></textarea>
                </div>

                {{-- Items list --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-molveno-blue-500 px-4 py-3 text-white">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold">{{ $table->table_name }}</span>
                            <span class="text-xs opacity-75" x-text="new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})"></span>
                        </div>
                    </div>

                    <template x-for="(item, dishId) in cart" :key="dishId">
                        <div class="px-4 py-3 border-b border-gray-100 last:border-0">
                            <div class="flex items-start gap-3">
                                <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 bg-gray-300"></span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-baseline justify-between gap-2">
                                        <span class="text-sm font-semibold text-gray-800" x-text="item.name"></span>
                                        <span class="text-xs text-gray-400 shrink-0">&times;<span x-text="item.qty"></span></span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5" x-text="'€ ' + (item.price * item.qty).toFixed(2)"></p>
                                    <p x-show="item.notes" class="text-xs text-gray-400 italic mt-0.5" x-text="`&quot;` + item.notes + `&quot;`"></p>
                                </div>
                            </div>
                            <div class="mt-2 pl-5 flex items-center gap-2">
                                <button type="button" class="w-6 h-6 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 transition-colors" @click="changeQty(dishId, -1)">
                                    <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M5 12h14"/></svg>
                                </button>
                                <span class="text-sm font-bold text-gray-700 w-5 text-center" x-text="item.qty"></span>
                                <button type="button" class="w-6 h-6 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 transition-colors" @click="changeQty(dishId, 1)">
                                    <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                                </button>
                                <button type="button" class="ms-auto text-gray-300 hover:text-red-500 transition-colors" @click="removeItem(dishId)">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>

                    <div class="px-4 py-2.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-xs text-gray-400" x-text="Object.keys(cart).length + ' ' + (Object.keys(cart).length === 1 ? 'dish' : 'dishes')"></span>
                        <span class="text-xs font-semibold text-gray-600">&euro;&nbsp;<span x-text="orderTotal.toFixed(2)"></span></span>
                    </div>
                </div>

            </div>
        </div>

        {{-- Action bar --}}
        <div class="shrink-0 bg-white border-t border-gray-200 shadow-[0_-4px_16px_rgba(0,0,0,.06)]">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-4">
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-500">Order total</p>
                    <p class="text-lg font-black text-gray-900 leading-tight">&euro;&nbsp;<span x-text="orderTotal.toFixed(2)"></span></p>
                </div>
                <x-ui.button size="lg" class="shrink-0" x-on:click="submitOrder()" x-bind:disabled="submitting" x-bind:class="submitting ? 'opacity-50 cursor-not-allowed' : ''">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    <span x-text="submitting ? 'Placing…' : 'Place Order'"></span>
                </x-ui.button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         Alpine.js cart logic
    ══════════════════════════════════════════════════════════ --}}
    <script>
        function orderCart() {
            return {
                sidebarOpen: false,
                addModal: { open: false, dishId: null, name: '', price: 0, qty: 1, notes: '' },
                reviewOpen: false,
                submitting: false,
                orderNotes: '',

                /** @type {Object.<number, {name: string, price: number, qty: number, notes: string}>} */
                cart: {},

                get itemCount() {
                    return Object.values(this.cart).reduce((sum, item) => sum + item.qty, 0);
                },

                get orderTotal() {
                    return Object.values(this.cart).reduce((sum, item) => sum + (item.price * item.qty), 0);
                },

                init() {},

                openAddModal(dishId, name, price, allergens, dietary) {
                    this.addModal = {
                        open: true,
                        dishId,
                        name,
                        price,
                        qty: 1,
                        notes: this.cart[dishId]?.notes ?? '',
                    };
                },

                closeAddModal() {
                    this.addModal.open = false;
                },

                confirmAdd() {
                    const id = this.addModal.dishId;
                    if (this.cart[id]) {
                        this.cart[id].qty += this.addModal.qty;
                        if (this.addModal.notes.trim()) {
                            this.cart[id].notes = this.addModal.notes.trim();
                        }
                    } else {
                        this.cart[id] = {
                            name: this.addModal.name,
                            price: this.addModal.price,
                            qty: this.addModal.qty,
                            notes: this.addModal.notes.trim(),
                        };
                    }
                    this.closeAddModal();
                },

                changeQty(dishId, delta) {
                    if (!this.cart[dishId]) { return; }
                    const newQty = this.cart[dishId].qty + delta;
                    if (newQty < 1) {
                        this.removeItem(dishId);
                    } else {
                        this.cart[dishId].qty = newQty;
                    }
                },

                removeItem(dishId) {
                    delete this.cart[dishId];
                    this.cart = { ...this.cart };
                },

                openReview() {
                    if (this.itemCount === 0) { return; }
                    this.reviewOpen = true;
                },

                submitOrder() {
                    if (this.itemCount === 0 || this.submitting) { return; }
                    this.submitting = true;

                    const cartItems = Object.entries(this.cart).map(([dishId, item]) => ({
                        dish_id: parseInt(dishId),
                        qty: item.qty,
                        notes: item.notes,
                    }));

                    this.$wire.placeOrder(cartItems, this.orderNotes).then(() => {
                        this.submitting = false;
                    }).catch(() => {
                        this.submitting = false;
                    });
                },
            };
        }
    </script>
</div>
