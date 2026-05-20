<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-bold text-gray-900">Ingredient Library</h2>
                <button
                    type="button"
                    x-data
                    x-on:click="$dispatch('open-sheet', { name: 'help-dishes-ingredients' })"
                    class="p-1.5 rounded-lg text-gray-400 hover:text-primary hover:bg-gray-100 transition-colors"
                    title="How the Ingredient Library works"
                    aria-label="Open ingredients help"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01"/>
                        <circle cx="12" cy="20" r="1" fill="currentColor"/>
                    </svg>
                </button>
            </div>
            <p class="text-xs text-gray-500">Manage global ingredients shared across all dishes.</p>
        </div>
        <x-ui.button wire:click="$dispatch('open-ingredient-sheet')" size="sm" title="Add a new ingredient to the global library">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            Add Ingredient
        </x-ui.button>
    </div>
    <x-help.sheet page="dishes-ingredients" title="How the Ingredient Library works" />

    {{-- Search --}}
    <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Search ingredients…" class="mb-4" />

    @error('delete')
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3 mb-4">
            {{ $message }}
        </div>
    @enderror

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        <x-ui.table>
            <thead>
                <tr>
                    <x-ui.th>Name</x-ui.th>
                    <x-ui.th>Allergens</x-ui.th>
                    <x-ui.th>Dietary</x-ui.th>
                    <x-ui.th class="text-center">Dishes</x-ui.th>
                    <x-ui.th class="text-center">Available</x-ui.th>
                    <x-ui.th class="text-right">Actions</x-ui.th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->ingredients as $ingredient)
                    <tr wire:key="ing-{{ $ingredient->id }}" class="hover:bg-gray-50">
                        <x-ui.td class="font-medium text-gray-900">
                            <div class="flex items-center gap-2">
                                {{ $ingredient->name }}
                                @if(in_array($ingredient->id, $newIngredientIds))
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">New</span>
                                @elseif(in_array($ingredient->id, $updatedIngredientIds))
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">Updated</span>
                                @endif
                            </div>
                        </x-ui.td>
                        <x-ui.td>
                            <div class="flex items-center gap-1">
                                @foreach($ingredient->allergens as $a)
                                    @if(isset($allergenConfig[$a]))
                                        <x-dishes.allergen-icon :bg="$allergenConfig[$a]['bg']" :icon="$allergenConfig[$a]['icon']" :title="$allergenConfig[$a]['label']" size="sm" />
                                    @endif
                                @endforeach
                                @if(empty($ingredient->allergens))
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </div>
                        </x-ui.td>
                        <x-ui.td>
                            <div class="flex items-center gap-1">
                                @foreach($ingredient->dietary as $d)
                                    <x-dishes.dietary-icon :type="$d" size="sm" />
                                @endforeach
                                @if(empty($ingredient->dietary))
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </div>
                        </x-ui.td>
                        <x-ui.td class="text-center">
                            <span class="text-xs font-medium {{ $ingredient->dishes_count > 0 ? 'text-molveno-blue-700' : 'text-gray-400' }}">
                                {{ $ingredient->dishes_count }}
                            </span>
                        </x-ui.td>
                        <x-ui.td class="text-center">
                            <button
                                type="button"
                                wire:click="toggleAvailability({{ $ingredient->id }})"
                                @class([
                                    'relative inline-flex h-5 w-9 items-center rounded-full transition-colors',
                                    'bg-green-500' => $ingredient->is_available,
                                    'bg-gray-300' => ! $ingredient->is_available,
                                ])
                                title="{{ $ingredient->is_available ? 'Available — click to mark out of stock' : 'Out of stock — click to mark available' }}"
                                aria-label="Toggle availability for {{ $ingredient->name }}"
                            >
                                <span @class([
                                    'inline-block h-3 w-3 transform rounded-full bg-white transition-transform',
                                    'translate-x-5' => $ingredient->is_available,
                                    'translate-x-1' => ! $ingredient->is_available,
                                ])></span>
                            </button>
                        </x-ui.td>
                        <x-ui.td class="text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button
                                    wire:click="$dispatch('open-ingredient-sheet', { ingredientId: {{ $ingredient->id }} })"
                                    class="p-1.5 rounded-md text-gray-400 hover:text-molveno-blue-700 hover:bg-molveno-blue-500/5"
                                    title="Edit"
                                >
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                        <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                    </svg>
                                </button>
                                <button
                                    wire:click="deleteIngredient({{ $ingredient->id }})"
                                    wire:confirm="Delete {{ $ingredient->name }}? This cannot be undone."
                                    class="p-1.5 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 {{ $ingredient->dishes_count > 0 ? 'opacity-30 cursor-not-allowed' : '' }}"
                                    title="{{ $ingredient->dishes_count > 0 ? 'In use by ' . $ingredient->dishes_count . ' dish(es)' : 'Delete' }}"
                                    @if($ingredient->dishes_count > 0) disabled @endif
                                >
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                        <path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14"/>
                                    </svg>
                                </button>
                            </div>
                        </x-ui.td>
                    </tr>
                @empty
                    <tr>
                        <x-ui.td colspan="6" class="text-center text-gray-400 py-8">
                            No ingredients found. Create one to get started.
                        </x-ui.td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>
    </div>

    {{-- Pagination --}}
    @if($this->ingredients->hasPages())
        <div class="mt-4">
            {{ $this->ingredients->links() }}
        </div>
    @endif
</div>
