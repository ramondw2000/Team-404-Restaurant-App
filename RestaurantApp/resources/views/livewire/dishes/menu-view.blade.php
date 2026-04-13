<div>
    {{-- Menu Header --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
        @if($editingMenu)
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Menu Name</label>
                    <x-ui.input wire:model="menuName" :error="$errors->has('menuName')" />
                    @error('menuName')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Description</label>
                    <x-ui.input type="textarea" wire:model="menuDescription" rows="2" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                    <x-ui.input type="select" wire:model="menuStatus">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </x-ui.input>
                </div>
                <div class="flex gap-2">
                    <x-ui.button wire:click="saveMenu" size="sm">Save</x-ui.button>
                    <x-ui.button variant="secondary" wire:click="$set('editingMenu', false)" size="sm">Cancel</x-ui.button>
                </div>
            </div>
        @else
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-bold text-gray-900">{{ $this->menu->name }}</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                            {{ $this->menu->isPublished() ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $this->menu->isPublished() ? 'Published' : 'Draft' }}
                        </span>
                    </div>
                    @if($this->menu->description)
                        <p class="text-sm text-gray-500 mt-1">{{ $this->menu->description }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <x-ui.button
                        wire:click="toggleStatus"
                        variant="outline"
                        size="sm"
                    >
                        {{ $this->menu->isPublished() ? 'Unpublish' : 'Publish' }}
                    </x-ui.button>
                    <x-ui.button wire:click="$set('editingMenu', true)" variant="secondary" size="sm">Edit</x-ui.button>
                    <x-ui.button
                        wire:click="deleteMenu"
                        wire:confirm="Delete this menu? All dish assignments will be removed."
                        variant="danger"
                        size="sm"
                    >Delete</x-ui.button>
                </div>
            </div>
        @endif
    </div>

    {{-- Categories with dishes --}}
    @foreach($this->menu->categories as $category)
        <div
            wire:key="menu-cat-{{ $category->id }}"
            class="mb-6 {{ $focusCategoryId === $category->id ? 'ring-2 ring-molveno-blue-300 rounded-2xl' : '' }}"
        >
            {{-- Category header --}}
            <div class="flex items-center justify-between mb-3">
                @if($renamingCategoryId === $category->id)
                    <form wire:submit="renameCategory" class="flex items-center gap-2">
                        <x-ui.input wire:model="renameCategoryName" class="text-sm py-1" :error="$errors->has('renameCategoryName')" />
                        <x-ui.button type="submit" size="sm">Save</x-ui.button>
                        <x-ui.button variant="secondary" wire:click="$set('renamingCategoryId', null)" size="sm">Cancel</x-ui.button>
                    </form>
                @else
                    <h3 class="text-base font-bold text-gray-800">{{ $category->name }}</h3>
                    <div class="flex items-center gap-1.5">
                        <button
                            wire:click="openAddDishes({{ $category->id }})"
                            class="text-xs font-medium text-molveno-blue-500 hover:underline"
                        >+ Add Dishes</button>
                        <button
                            wire:click="startRenameCategory({{ $category->id }})"
                            class="p-1 rounded text-gray-400 hover:text-gray-600"
                            title="Rename"
                        >
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                            </svg>
                        </button>
                        <button
                            wire:click="deleteCategory({{ $category->id }})"
                            wire:confirm="Delete category '{{ $category->name }}'? Dishes will be unassigned from this menu."
                            class="p-1 rounded text-gray-400 hover:text-red-500"
                            title="Delete category"
                        >
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14"/>
                            </svg>
                        </button>
                    </div>
                @endif
            </div>

            {{-- Add dishes panel --}}
            @if($addingDishesToCategoryId === $category->id)
                <div class="bg-gray-50 rounded-xl p-4 mb-3 border border-gray-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-gray-600">Add dishes to {{ $category->name }}</span>
                        <button wire:click="closeAddDishes" class="text-xs text-gray-400 hover:text-gray-600">Close</button>
                    </div>
                    <x-ui.input wire:model.live.debounce.200ms="dishSearch" placeholder="Search dishes…" class="text-xs mb-2" />
                    <div class="max-h-48 overflow-y-auto space-y-1">
                        @forelse($this->availableDishes as $dish)
                            <button
                                wire:key="add-dish-{{ $dish->id }}"
                                wire:click="assignDish({{ $dish->id }})"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm hover:bg-white transition-colors"
                            >
                                <span class="font-medium text-gray-700">{{ $dish->name }}</span>
                                <span class="text-xs text-gray-400">&euro;{{ number_format($dish->price, 2) }}</span>
                            </button>
                        @empty
                            <p class="text-xs text-gray-400 px-3 py-2">No dishes found.</p>
                        @endforelse
                    </div>
                </div>
            @endif

            {{-- Dish cards in category --}}
            @if($category->dishes->isNotEmpty())
                <div wire:sort="reorderDish" class="grid gap-3 grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5">
                    @foreach($category->dishes as $dish)
                        <div
                            wire:key="cat-dish-{{ $category->id }}-{{ $dish->id }}"
                            wire:sort:item="{{ $dish->id }}"
                            class="group relative rounded-xl shadow-sm overflow-hidden flex flex-col cursor-pointer hover:shadow-md transition-shadow"
                        >
                            {{-- Image --}}
                            <div class="h-24 flex items-center justify-center overflow-hidden" style="background-color: {{ $dish->color }}">
                                @if($dish->photo_path)
                                    <img src="{{ asset('storage/' . $dish->photo_path) }}" alt="{{ $dish->name }}" class="w-full h-full object-cover">
                                @else
                                    <svg class="opacity-30" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="13" r="8"/>
                                        <path d="M7 5v3M8 5v3M7.5 8v5"/>
                                    </svg>
                                @endif
                            </div>
                            {{-- Info --}}
                            <div class="bg-white px-2.5 py-2 flex-1 flex flex-col gap-0.5">
                                <p class="font-bold text-molveno-blue-700 text-xs leading-tight line-clamp-1">{{ $dish->name }}</p>
                                <p class="text-primary font-black text-xs">&euro;{{ number_format($dish->price, 2) }}</p>
                                <div class="flex items-center gap-0.5 flex-wrap">
                                    @foreach($dish->allergens as $a)
                                        @if(isset($allergenConfig[$a]))
                                            <x-dishes.allergen-icon :bg="$allergenConfig[$a]['bg']" :icon="$allergenConfig[$a]['icon']" size="sm" shadow />
                                        @endif
                                    @endforeach
                                    @foreach($dish->dietary as $d)
                                        <x-dishes.dietary-icon :type="$d" size="sm" shadow />
                                    @endforeach
                                </div>
                            </div>
                            {{-- Remove button --}}
                            <button
                                wire:click="removeDish({{ $category->id }}, {{ $dish->id }})"
                                class="absolute top-1 right-1 w-6 h-6 rounded-full bg-black/40 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-500"
                                title="Remove from category"
                            >
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">
                                    <path d="M18 6 6 18M6 6l12 12"/>
                                </svg>
                            </button>
                            {{-- Drag handle --}}
                            <div wire:sort:handle class="absolute top-1 left-1 w-6 h-6 rounded-full bg-black/20 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-grab">
                                <svg width="10" height="10" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7 4a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm6 0a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm-6 6a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm6 0a1.5 1.5 0 110-3 1.5 1.5 0 010 3z"/>
                                </svg>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                    <p class="text-sm text-gray-400">No dishes in this category.</p>
                    <button
                        wire:click="openAddDishes({{ $category->id }})"
                        class="mt-2 text-xs font-medium text-molveno-blue-500 hover:underline"
                    >Add dishes</button>
                </div>
            @endif
        </div>
    @endforeach

    {{-- Add category --}}
    @if($showAddCategory)
        <div class="mt-4">
            <form wire:submit="addCategory" class="flex items-center gap-2">
                <x-ui.input wire:model="newCategoryName" placeholder="Category name…" class="text-sm" :error="$errors->has('newCategoryName')" />
                <x-ui.button type="submit" size="sm">Add</x-ui.button>
                <x-ui.button variant="secondary" wire:click="$set('showAddCategory', false)" size="sm">Cancel</x-ui.button>
            </form>
            @error('newCategoryName')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>
    @else
        <button
            wire:click="$set('showAddCategory', true)"
            class="mt-4 text-sm font-medium text-molveno-blue-500 hover:underline"
        >+ Add Category</button>
    @endif
</div>
