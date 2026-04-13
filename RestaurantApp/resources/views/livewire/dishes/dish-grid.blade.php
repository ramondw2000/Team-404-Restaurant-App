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
            <button
                wire:click="toggleDietary('vegetarian')"
                class="filter-btn inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-full border transition-colors
                    {{ in_array('vegetarian', $dietaryFilters) ? 'bg-primary border-primary text-white' : 'bg-white border-gray-300 text-gray-700 hover:border-molveno-blue-500' }}"
            >
                <x-dishes.dietary-icon type="vegetarian" size="sm" />
                Vegetarian
            </button>
            <button
                wire:click="toggleDietary('vegan')"
                class="filter-btn inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-full border transition-colors
                    {{ in_array('vegan', $dietaryFilters) ? 'bg-primary border-primary text-white' : 'bg-white border-gray-300 text-gray-700 hover:border-molveno-blue-500' }}"
            >
                <x-dishes.dietary-icon type="vegan" size="sm" />
                Vegan
            </button>

            <span class="text-gray-300 hidden sm:inline">|</span>
            <span class="text-xs font-semibold text-gray-500 shrink-0">Free from:</span>
            @foreach($allergenConfig as $key => $cfg)
                <button
                    wire:click="toggleFreeFrom('{{ $key }}')"
                    class="filter-btn inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-full border transition-colors
                        {{ in_array($key, $freeFromFilters) ? 'bg-primary border-primary text-white' : 'bg-white border-gray-300 text-gray-700 hover:border-molveno-blue-500' }}"
                >
                    <x-dishes.allergen-icon :bg="$cfg['bg']" :icon="$cfg['icon']" size="sm" />
                    {{ $cfg['label'] }}-free
                </button>
            @endforeach
        </div>

        {{-- Sort + page size --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500">Sort:</span>
                <button wire:click="setSort('name')" class="text-xs font-medium {{ $sortBy === 'name' ? 'text-molveno-blue-700 underline' : 'text-gray-600 hover:text-gray-900' }}">
                    Name {!! $sortBy === 'name' ? ($sortDir === 'asc' ? '↑' : '↓') : '' !!}
                </button>
                <button wire:click="setSort('price')" class="text-xs font-medium {{ $sortBy === 'price' ? 'text-molveno-blue-700 underline' : 'text-gray-600 hover:text-gray-900' }}">
                    Price {!! $sortBy === 'price' ? ($sortDir === 'asc' ? '↑' : '↓') : '' !!}
                </button>
                <button wire:click="setSort('created_at')" class="text-xs font-medium {{ $sortBy === 'created_at' ? 'text-molveno-blue-700 underline' : 'text-gray-600 hover:text-gray-900' }}">
                    Newest {!! $sortBy === 'created_at' ? ($sortDir === 'asc' ? '↑' : '↓') : '' !!}
                </button>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="text-xs text-gray-500">Show:</span>
                @foreach([12, 20, 40] as $size)
                    <button
                        wire:click="setPerPage({{ $size }})"
                        class="text-xs px-2 py-1 rounded {{ $perPage === $size ? 'bg-molveno-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}"
                    >{{ $size }}</button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Grid --}}
    <div class="grid gap-4 justify-center mt-4 dish-grid">
        @forelse($this->dishes as $dish)
            <div
                wire:key="dish-{{ $dish->id }}"
                wire:click="$parent.openDishSheet({{ $dish->id }})"
                class="dish-card rounded-2xl shadow-md overflow-hidden flex flex-col cursor-pointer select-none transition-all duration-150 hover:shadow-xl hover:-translate-y-1"
            >
                {{-- Image / placeholder --}}
                <div class="flex-1 flex items-center justify-center overflow-hidden"
                     style="background-color: {{ $dish->color }}">
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
                    <p class="font-bold text-molveno-blue-700 text-xs leading-tight line-clamp-2">{{ $dish->name }}</p>
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
