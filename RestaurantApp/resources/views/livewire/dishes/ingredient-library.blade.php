<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-lg font-bold text-gray-900">Ingredient Library</h2>
            <p class="text-xs text-gray-500">Manage global ingredients shared across all dishes.</p>
        </div>
        <x-ui.button wire:click="openCreateForm" size="sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            Add Ingredient
        </x-ui.button>
    </div>

    {{-- Search --}}
    <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Search ingredients…" class="mb-4" />

    {{-- Inline create/edit form --}}
    @if($showForm)
        <div class="bg-gray-50 rounded-xl p-4 mb-4 border border-gray-100">
            <h3 class="text-sm font-bold text-gray-800 mb-3">{{ $editingId ? 'Edit Ingredient' : 'New Ingredient' }}</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Name <span class="text-red-400">*</span></label>
                    <x-ui.input wire:model="formName" placeholder="Ingredient name" :error="$errors->has('formName')" />
                    @error('formName')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Allergens</label>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($allergenConfig as $key => $cfg)
                            <label class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full border text-xs font-medium cursor-pointer transition-colors
                                {{ in_array($key, $formAllergens) ? 'bg-molveno-blue-500/10 border-molveno-blue-500 text-molveno-blue-700' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                                <input type="checkbox" wire:model.live="formAllergens" value="{{ $key }}" class="hidden">
                                <x-dishes.allergen-icon :bg="$cfg['bg']" :icon="$cfg['icon']" size="sm" />
                                {{ $cfg['label'] }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Dietary</label>
                    <div class="flex gap-1.5">
                        @foreach(['vegetarian', 'vegan'] as $d)
                            <label class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full border text-xs font-medium cursor-pointer transition-colors
                                {{ in_array($d, $formDietary) ? 'bg-green-50 border-green-500 text-green-700' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                                <input type="checkbox" wire:model.live="formDietary" value="{{ $d }}" class="hidden">
                                <x-dishes.dietary-icon :type="$d" size="sm" />
                                {{ ucfirst($d) }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex gap-2">
                    <x-ui.button wire:click="saveIngredient" size="sm">{{ $editingId ? 'Update' : 'Create' }}</x-ui.button>
                    <x-ui.button variant="secondary" wire:click="resetForm" size="sm">Cancel</x-ui.button>
                </div>
            </div>
        </div>
    @endif

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
                    <x-ui.th class="text-right">Actions</x-ui.th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->ingredients as $ingredient)
                    <tr wire:key="ing-{{ $ingredient->id }}" class="hover:bg-gray-50">
                        <x-ui.td class="font-medium text-gray-900">{{ $ingredient->name }}</x-ui.td>
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
                        <x-ui.td class="text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button
                                    wire:click="openEditForm({{ $ingredient->id }})"
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
                        <x-ui.td colspan="5" class="text-center text-gray-400 py-8">
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
