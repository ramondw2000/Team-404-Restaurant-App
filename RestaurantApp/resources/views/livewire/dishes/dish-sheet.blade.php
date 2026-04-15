@php
    $allergenConfig = config('restaurant.allergens');
@endphp

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
                    {{ $dishId ? 'Edit Dish' : 'Add New Dish' }}
                </h2>
                <p class="text-xs text-gray-400 mt-0.5">
                    @if($dishId)
                        Editing: <span class="font-semibold text-gray-600">{{ $name }}</span>
                    @else
                        Fill in the details below to add a dish to the menu.
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

            {{-- Photo --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Photo</label>
                <div
                    x-data="{ dragover: false }"
                    x-on:dragover.prevent="dragover = true"
                    x-on:dragleave="dragover = false"
                    x-on:drop.prevent="
                        dragover = false;
                        const file = $event.dataTransfer.files[0];
                        if (file && file.type.startsWith('image/')) {
                            $refs.photoInput.files = $event.dataTransfer.files;
                            $refs.photoInput.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    "
                    class="relative w-full rounded-xl overflow-hidden cursor-pointer border-2 border-dashed transition-colors"
                    :class="dragover ? 'border-molveno-blue-500 bg-blue-50' : 'border-gray-200'"
                    style="aspect-ratio: 16/9; background-color: {{ $color }}"
                    wire:click="$refs?.photoInput?.click()"
                >
                    @if($photo && $photo->isPreviewable())
                        <img src="{{ $photo->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-cover" alt="Preview">
                    @elseif($existingPhotoPath)
                        <img src="{{ asset('storage/' . $existingPhotoPath) }}" class="absolute inset-0 w-full h-full object-cover" alt="{{ $name }}">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center gap-2">
                            <svg class="opacity-30" width="52" height="52" viewBox="0 0 24 24" fill="none"
                                 stroke="white" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="13" r="8"/>
                                <path d="M7 5v3M8 5v3M7.5 8v5"/>
                                <path d="M15 5c1 1 1.5 2 1.5 3v6"/>
                                <path d="M15 5c-1 1-1.5 2-1.5 3h3"/>
                            </svg>
                            <p class="text-sm font-medium text-white/70">Click or drag to upload</p>
                            <p class="text-xs text-white/50">PNG, JPG up to 5 MB</p>
                        </div>
                    @endif
                    <input
                        x-ref="photoInput"
                        type="file"
                        wire:model="photo"
                        accept="image/jpeg,image/png,image/webp,image/gif"
                        class="hidden"
                    >
                </div>
                @error('photo')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Name --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Dish Name <span class="text-red-400">*</span>
                </label>
                <x-ui.input wire:model="name" placeholder="e.g. Spaghetti Bolognese" :error="$errors->has('name')" />
                @error('name')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Description</label>
                <x-ui.input type="textarea" wire:model="description" rows="3" placeholder="Short description of the dish…" />
            </div>

            {{-- Price + Color --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Price <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400 font-medium pointer-events-none">&euro;</span>
                        <x-ui.input wire:model="price" type="number" min="0" step="0.01" class="!pl-7" placeholder="0.00" :error="$errors->has('price')" />
                    </div>
                    @error('price')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Card Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" wire:model.live="color" class="w-10 h-10 rounded-lg border border-gray-200 cursor-pointer p-0.5">
                        <x-ui.input wire:model.live="color" class="font-mono text-xs" maxlength="7" />
                    </div>
                </div>
            </div>

            {{-- Ingredients --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Ingredients</label>
                <p class="text-xs text-gray-400 mb-2.5">Allergens and dietary info are derived from ingredients.</p>

                {{-- Selected ingredients --}}
                @if($ingredientIds !== [])
                    <div class="flex flex-wrap gap-1.5 mb-3">
                        @foreach($this->selectedIngredients as $ingredient)
                            <span
                                wire:key="sel-ing-{{ $ingredient->id }}"
                                class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 rounded-full text-xs font-medium text-gray-700"
                            >
                                {{ $ingredient->name }}
                                <button wire:click="removeIngredient({{ $ingredient->id }})" class="text-gray-400 hover:text-red-500">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                        <path d="M18 6 6 18M6 6l12 12"/>
                                    </svg>
                                </button>
                            </span>
                        @endforeach
                    </div>
                @endif

                {{-- Search ingredients --}}
                <div class="relative">
                    <x-ui.input
                        wire:model.live.debounce.200ms="ingredientSearch"
                        placeholder="Search ingredients…"
                        class="text-xs"
                    />
                    @if($ingredientSearch !== '')
                        <div class="absolute z-10 mt-1 w-full bg-white rounded-lg shadow-lg border border-gray-100 max-h-40 overflow-y-auto">
                            @forelse($this->availableIngredients as $ing)
                                <button
                                    wire:key="avail-ing-{{ $ing->id }}"
                                    wire:click="addIngredient({{ $ing->id }})"
                                    class="w-full px-3 py-2 text-left text-sm hover:bg-gray-50 flex items-center justify-between"
                                >
                                    <span>{{ $ing->name }}</span>
                                    <span class="flex items-center gap-1">
                                        @foreach($ing->allergens as $a)
                                            @if(isset($allergenConfig[$a]))
                                                <x-dishes.allergen-icon :bg="$allergenConfig[$a]['bg']" :icon="$allergenConfig[$a]['icon']" size="sm" />
                                            @endif
                                        @endforeach
                                    </span>
                                </button>
                            @empty
                                <div class="px-3 py-2 text-xs text-gray-400">No ingredients found.</div>
                            @endforelse
                        </div>
                    @endif
                </div>

                {{-- Quick-create ingredient --}}
                <button
                    wire:click="$toggle('showNewIngredientForm')"
                    class="mt-2 text-xs font-medium text-molveno-blue-500 hover:underline"
                >
                    {{ $showNewIngredientForm ? 'Cancel' : '+ Create new ingredient' }}
                </button>

                @if($showNewIngredientForm)
                    <div class="mt-2 p-3 bg-gray-50 rounded-lg space-y-3">
                        <x-ui.input wire:model="newIngredientName" placeholder="Ingredient name" class="text-xs" :error="$errors->has('newIngredientName')" />
                        @error('newIngredientName')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                        <div>
                            <span class="text-xs font-medium text-gray-600">Allergens:</span>
                            <div class="flex flex-wrap gap-1.5 mt-1">
                                @foreach($allergenConfig as $key => $cfg)
                                    <label class="inline-flex items-center gap-1 px-2 py-1 rounded-full border text-xs cursor-pointer transition-colors
                                        {{ in_array($key, $newIngredientAllergens) ? 'bg-molveno-blue-500/10 border-molveno-blue-500 text-molveno-blue-700' : 'border-gray-200 text-gray-600' }}">
                                        <input type="checkbox" wire:model.live="newIngredientAllergens" value="{{ $key }}" class="hidden">
                                        <x-dishes.allergen-icon :bg="$cfg['bg']" :icon="$cfg['icon']" size="sm" />
                                        {{ $cfg['label'] }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-gray-600">Dietary:</span>
                            <div class="flex gap-1.5 mt-1">
                                @foreach(['vegetarian', 'vegan'] as $d)
                                    <label class="inline-flex items-center gap-1 px-2 py-1 rounded-full border text-xs cursor-pointer transition-colors
                                        {{ in_array($d, $newIngredientDietary) ? 'bg-green-50 border-green-500 text-green-700' : 'border-gray-200 text-gray-600' }}">
                                        <input type="checkbox" wire:model.live="newIngredientDietary" value="{{ $d }}" class="hidden">
                                        <x-dishes.dietary-icon :type="$d" size="sm" />
                                        {{ ucfirst($d) }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <x-ui.button wire:click="createIngredient" size="sm">Add Ingredient</x-ui.button>
                    </div>
                @endif

                {{-- Derived allergens/dietary summary --}}
                @if($ingredientIds !== [])
                    <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                        <p class="text-xs font-semibold text-gray-500 mb-1.5">Auto-derived from ingredients:</p>
                        <div class="flex flex-wrap items-center gap-2">
                            @if($this->derivedAllergens !== [])
                                <span class="text-xs text-gray-400">Allergens:</span>
                                @foreach($this->derivedAllergens as $a)
                                    @if(isset($allergenConfig[$a]))
                                        <div class="inline-flex items-center gap-1">
                                            <x-dishes.allergen-icon :bg="$allergenConfig[$a]['bg']" :icon="$allergenConfig[$a]['icon']" size="sm" />
                                            <span class="text-xs text-gray-600">{{ $allergenConfig[$a]['label'] }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                <span class="text-xs text-gray-400">No allergens</span>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-2 mt-1.5">
                            <span class="text-xs text-gray-400">Dietary:</span>
                            @forelse($this->derivedDietary as $d)
                                <div class="inline-flex items-center gap-1">
                                    <x-dishes.dietary-icon :type="$d" size="sm" />
                                    <span class="text-xs text-gray-600">{{ ucfirst($d) }}</span>
                                </div>
                            @empty
                                <span class="text-xs text-gray-400">None</span>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>

            {{-- Menu Assignments --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Menu Assignments</label>
                <p class="text-xs text-gray-400 mb-2.5">Assign this dish to one or more menu categories.</p>

                @foreach($menuAssignments as $idx => $assignment)
                    <div wire:key="assign-{{ $idx }}" class="flex items-center gap-2 mb-2">
                        <select
                            wire:model.live="menuAssignments.{{ $idx }}.menu_id"
                            class="flex-1 border border-gray-200 rounded-lg px-3 py-1.5 text-xs"
                        >
                            <option value="0">Select menu…</option>
                            @foreach($this->menus as $menu)
                                <option value="{{ $menu->id }}">{{ $menu->name }}</option>
                            @endforeach
                        </select>
                        @if($assignment['menu_id'])
                            <select
                                wire:model="menuAssignments.{{ $idx }}.category_id"
                                class="flex-1 border border-gray-200 rounded-lg px-3 py-1.5 text-xs"
                            >
                                <option value="">Select category…</option>
                                @foreach($this->menus->find($assignment['menu_id'])?->categories ?? [] as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        @endif
                        <button
                            wire:click="removeMenuAssignment({{ $idx }})"
                            class="shrink-0 w-7 h-7 flex items-center justify-center rounded-md text-gray-400 hover:text-red-500 hover:bg-red-50"
                        >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <path d="M18 6 6 18M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @endforeach

                <button
                    wire:click="addMenuAssignment"
                    class="text-xs font-medium text-molveno-blue-500 hover:underline"
                >
                    + Add to menu
                </button>
            </div>
        </div>

        {{-- Footer --}}
        <div class="shrink-0 border-t border-gray-100 px-6 py-4 flex items-center gap-3 bg-gray-50">
            @if($dishId)
                <x-ui.button
                    variant="danger"
                    size="sm"
                    wire:click="deleteDish"
                    wire:confirm="Are you sure you want to delete this dish?"
                    class="mr-auto"
                >
                    Delete Dish
                </x-ui.button>
            @endif
            <x-ui.button variant="secondary" wire:click="close" class="{{ $dishId ? '' : 'ml-auto' }}" size="sm">
                Cancel
            </x-ui.button>
            <x-ui.button wire:click="save" size="sm">
                {{ $dishId ? 'Update Dish' : 'Save Dish' }}
            </x-ui.button>
        </div>
    </div>
</div>
