<div>
    {{-- Legend --}}
    <x-dishes.allergen-legend :allergenConfig="$allergenConfig" />

    {{-- Filters --}}
    <div class="flex flex-col gap-3 mt-4">
        {{-- Search --}}
        <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Search dishes…" />

        {{-- Dietary + free-from pills --}}
        <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
            <span class="text-xs font-semibold text-gray-500 shrink-0">Dietary:</span>

            <x-dishes.filter-pill
                filter="dietary"
                value="vegetarian"
                wire:click="toggleDietary('vegetarian')"
                @class(['filter-active' => in_array('vegetarian', $dietaryFilters)])
            >
                <x-dishes.dietary-icon type="vegetarian" size="sm" />
                Vegetarian
            </x-dishes.filter-pill>

            <x-dishes.filter-pill
                filter="dietary"
                value="vegan"
                wire:click="toggleDietary('vegan')"
                @class(['filter-active' => in_array('vegan', $dietaryFilters)])
            >
                <x-dishes.dietary-icon type="vegan" size="sm" />
                Vegan
            </x-dishes.filter-pill>

            <span class="text-gray-300 hidden sm:inline">|</span>
            <span class="text-xs font-semibold text-gray-500 shrink-0">Free from:</span>

            @foreach($allergenConfig as $key => $cfg)
                <x-dishes.filter-pill
                    filter="freefrom"
                    :value="$key"
                    wire:key="freefrom-pill-{{ $key }}"
                    wire:click="toggleFreeFrom('{{ $key }}')"
                    @class(['filter-active' => in_array($key, $freeFromFilters)])
                >
                    <x-dishes.allergen-icon :bg="$cfg['bg']" :icon="$cfg['icon']" size="sm" />
                    {{ $cfg['label'] }}-free
                </x-dishes.filter-pill>
            @endforeach
        </div>

        {{-- Sort + page size --}}
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-gray-500 shrink-0">Sort:</span>
                <x-ui.tab-group>
                    <x-ui.tab :active="$sortBy === 'name'" wire:click="setSort('name')">
                        Name @if($sortBy === 'name') {{ $sortDir === 'asc' ? '↑' : '↓' }} @endif
                    </x-ui.tab>
                    <x-ui.tab :active="$sortBy === 'price'" wire:click="setSort('price')">
                        Price @if($sortBy === 'price') {{ $sortDir === 'asc' ? '↑' : '↓' }} @endif
                    </x-ui.tab>
                    <x-ui.tab :active="$sortBy === 'created_at'" wire:click="setSort('created_at')">
                        Newest @if($sortBy === 'created_at') {{ $sortDir === 'asc' ? '↑' : '↓' }} @endif
                    </x-ui.tab>
                    <x-ui.tab :active="$sortBy === 'popularity'" wire:click="setSort('popularity')">
                        @if($sortBy === 'popularity' && $sortDir === 'asc')
                            Least Popular ↓
                        @elseif($sortBy === 'popularity')
                            Most Popular ↑
                        @else
                            Most Popular
                        @endif
                    </x-ui.tab>
                </x-ui.tab-group>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-gray-500 shrink-0">Show:</span>
                <x-ui.tab-group>
                    @foreach([12, 20, 40] as $size)
                        <x-ui.tab :active="$perPage === $size" wire:click="setPerPage({{ $size }})">
                            {{ $size }}
                        </x-ui.tab>
                    @endforeach
                </x-ui.tab-group>
            </div>
        </div>
    </div>

    {{-- Grid --}}
    <div class="grid gap-4 justify-center mt-4 dish-grid">
        @forelse($this->dishes as $dish)
            <div
                wire:key="dish-{{ $dish->id }}"
                wire:click="$parent.openDishSheet({{ $dish->id }})"
                class="dish-card rounded-2xl shadow-md overflow-hidden flex flex-col cursor-pointer select-none transition-all duration-150 hover:shadow-xl hover:-translate-y-1 {{ $dish->is_available ? '' : 'opacity-60' }}"
            >
                {{-- Image / placeholder --}}
                <div class="flex-1 flex items-center justify-center overflow-hidden relative"
                     style="background-color: {{ $dish->color }}">
                    @if(!$dish->is_available)
                        <div class="absolute inset-0 bg-black/20 flex items-center justify-center">
                            <span class="px-2 py-1 bg-black/60 text-white text-[10px] font-medium rounded">
                                Unavailable
                            </span>
                        </div>
                    @endif
                    @if($dish->photo_path)
                        <img src="{{ asset('storage/' . $dish->photo_path) }}"
                             alt="{{ $dish->name }}"
                             class="w-full h-full object-cover">
                    @else
                        <svg class="opacity-30" width="52" height="52" viewBox="0 0 24 24" fill="none"
                             stroke="white" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="13" r="8"/>
                            <path d="M7 5v3M8 5v3M7.5 8v5"/>
                            <path d="M15 5c1 1 1.5 2 1.5 3v6"/>
                            <path d="M15 5c-1 1-1.5 2-1.5 3h3"/>
                        </svg>
                    @endif
                </div>

                {{-- Info strip --}}
                <div class="shrink-0 bg-white px-3 py-2 flex flex-col gap-1">
                    <div class="flex items-start justify-between gap-1">
                        <p class="font-bold text-molveno-blue-700 text-xs leading-tight line-clamp-2">{{ $dish->name }}</p>
                        @if(!$dish->is_available)
                            <span class="shrink-0 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-200 text-gray-600">
                                Unavailable
                            </span>
                        @endif
                    </div>
                    <p class="text-primary font-black text-xs">&euro;{{ number_format($dish->price, 2) }}</p>
                    <div class="flex items-center gap-1 flex-wrap">
                        @foreach($dish->allergens as $allergen)
                            @if(isset($allergenConfig[$allergen]))
                                <x-dishes.allergen-icon
                                    :bg="$allergenConfig[$allergen]['bg']"
                                    :icon="$allergenConfig[$allergen]['icon']"
                                    :title="$allergenConfig[$allergen]['label']"
                                    shadow />
                            @endif
                        @endforeach
                        @if(in_array('vegetarian', $dish->dietary))
                            <x-dishes.dietary-icon type="vegetarian" shadow />
                        @endif
                        @if(in_array('vegan', $dish->dietary))
                            <x-dishes.dietary-icon type="vegan" shadow />
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <x-ui.empty-state title="No dishes match your filters." description="Try adjusting your search or filters.">
                    <x-slot:icon>
                        <svg class="w-10 h-10 text-gray-300 mb-3" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                        </svg>
                    </x-slot:icon>
                    <x-slot:action>
                        <x-ui.button variant="ghost" wire:click="resetFilters" size="sm">
                            Clear all filters
                        </x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($this->dishes->hasPages())
        <div class="mt-6">
            {{ $this->dishes->links() }}
        </div>
    @endif

    <x-dishes.styles />
</div>
