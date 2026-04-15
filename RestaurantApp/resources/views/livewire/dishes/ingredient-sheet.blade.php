<div>
    {{-- Overlay --}}
    <div
        wire:click="close"
        class="fixed inset-0 z-40 bg-black/30 backdrop-blur-sm transition-opacity"
    ></div>

    {{-- Panel --}}
    <div class="fixed top-0 right-0 z-50 w-full sm:max-w-lg bg-white shadow-2xl flex flex-col h-dvh">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
            <div>
                <h2 class="text-base font-bold text-gray-900">
                    {{ $ingredientId ? 'Edit Ingredient' : 'New Ingredient' }}
                </h2>
                <p class="text-xs text-gray-400 mt-0.5">
                    @if($ingredientId)
                        Editing: <span class="font-semibold text-gray-600">{{ $name }}</span>
                    @else
                        Create a new ingredient for use in dishes.
                    @endif
                </p>
            </div>
            <button wire:click="close"
                    class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2.5" stroke-linecap="round">
                    <path d="M18 6 6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto px-6 py-5 flex flex-col gap-5">

            @error('delete')
                <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
                    {{ $message }}
                </div>
            @enderror

            {{-- Name --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Name <span class="text-red-400">*</span>
                </label>
                <x-ui.input wire:model="name" placeholder="e.g. Aperol" :error="$errors->has('name')" />
                @error('name')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Allergens --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Allergens</label>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($allergenConfig as $key => $cfg)
                        <label class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full border text-xs font-medium cursor-pointer transition-colors
                            {{ in_array($key, $allergens) ? 'bg-molveno-blue-500/10 border-molveno-blue-500 text-molveno-blue-700' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                            <input type="checkbox" wire:model.live="allergens" value="{{ $key }}" class="hidden">
                            <x-dishes.allergen-icon :bg="$cfg['bg']" :icon="$cfg['icon']" size="sm" />
                            {{ $cfg['label'] }}
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Dietary --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Dietary</label>
                <div class="flex gap-1.5">
                    @foreach(['vegetarian', 'vegan'] as $d)
                        <label class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full border text-xs font-medium cursor-pointer transition-colors
                            {{ in_array($d, $dietary) ? 'bg-green-50 border-green-500 text-green-700' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                            <input type="checkbox" wire:model.live="dietary" value="{{ $d }}" class="hidden">
                            <x-dishes.dietary-icon :type="$d" size="sm" />
                            {{ ucfirst($d) }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="shrink-0 border-t border-gray-100 px-6 py-4 flex items-center gap-3 bg-gray-50">
            @if($ingredientId)
                <x-ui.button
                    variant="danger"
                    size="sm"
                    wire:click="deleteIngredient"
                    wire:confirm="Are you sure you want to delete this ingredient?"
                    class="mr-auto"
                >
                    Delete Ingredient
                </x-ui.button>
            @endif
            <x-ui.button variant="secondary" wire:click="close" class="{{ $ingredientId ? '' : 'ml-auto' }}" size="sm">
                Cancel
            </x-ui.button>
            <x-ui.button wire:click="save" size="sm">
                {{ $ingredientId ? 'Update' : 'Create' }}
            </x-ui.button>
        </div>
    </div>
</div>
