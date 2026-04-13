<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

    {{-- Library section --}}
    <div class="px-3 pt-4 pb-2">
        <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-2 mb-2">Library</h3>
        <nav class="flex flex-col gap-0.5">
            <button
                wire:click="navigate('dishes')"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                    {{ $activeView === 'dishes' ? 'bg-molveno-blue-500 text-white' : 'text-gray-700 hover:bg-gray-50' }}"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
                All Dishes
            </button>
            <button
                wire:click="navigate('ingredients')"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                    {{ $activeView === 'ingredients' ? 'bg-molveno-blue-500 text-white' : 'text-gray-700 hover:bg-gray-50' }}"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
                </svg>
                Ingredients
            </button>
        </nav>
    </div>

    <div class="border-t border-gray-100 mx-3"></div>

    {{-- Menus section --}}
    <div class="px-3 pt-3 pb-4">
        <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-2 mb-2">Menus</h3>
        <nav class="flex flex-col gap-0.5">
            @foreach($this->menus as $menu)
                <div wire:key="menu-{{ $menu->id }}">
                    {{-- Menu item --}}
                    <div
                        wire:click="navigate('menu', {{ $menu->id }})"
                        class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors cursor-pointer group
                            {{ $activeView === 'menu' && $activeMenuId === $menu->id && !$activeCategoryId ? 'bg-molveno-blue-500 text-white' : 'text-gray-700 hover:bg-gray-50' }}"
                    >
                        {{-- Expand toggle --}}
                        <span
                            wire:click.stop="toggleMenu({{ $menu->id }})"
                            class="shrink-0 w-4 h-4 flex items-center justify-center cursor-pointer"
                        >
                            <svg class="w-3 h-3 transition-transform {{ ($expandedMenus[$menu->id] ?? false) ? 'rotate-90' : '' }}"
                                 fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                        <span class="truncate {{ $menu->isDraft() ? 'italic opacity-70' : '' }}">{{ $menu->name }}</span>
                        @if($menu->isDraft())
                            <span class="ml-auto text-[10px] px-1.5 py-0.5 rounded-full {{ $activeView === 'menu' && $activeMenuId === $menu->id && !$activeCategoryId ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' }}">Draft</span>
                        @endif
                    </div>

                    {{-- Categories (expandable) --}}
                    @if($expandedMenus[$menu->id] ?? false)
                        <div
                            class="ml-6 flex flex-col gap-0.5 mt-0.5"
                            wire:sort="reorderCategory"
                        >
                            @foreach($menu->categories as $category)
                                <div
                                    wire:key="cat-{{ $category->id }}"
                                    wire:sort:item="{{ $category->id }}"
                                    class="w-full flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-medium transition-colors cursor-pointer
                                        {{ $activeCategoryId === $category->id ? 'bg-molveno-blue-100/50 text-molveno-blue-700' : 'text-gray-600 hover:bg-gray-50' }}"
                                >
                                    <svg wire:sort:handle class="w-3 h-3 text-gray-300 shrink-0 cursor-grab" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M7 4a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm6 0a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm-6 6a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm6 0a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm-6 6a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm6 0a1.5 1.5 0 110-3 1.5 1.5 0 010 3z"/>
                                    </svg>
                                    <span
                                        wire:click="navigate('menu', {{ $menu->id }}, {{ $category->id }})"
                                        wire:sort:ignore
                                        class="truncate flex-1"
                                    >{{ $category->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </nav>

        {{-- New Menu button / form --}}
        @if($showNewMenuForm)
            <div class="mt-2 px-2">
                <form wire:submit="createMenu" class="flex gap-1.5">
                    <x-ui.input
                        wire:model="newMenuName"
                        placeholder="Menu name…"
                        class="text-xs py-1.5"
                    />
                    <x-ui.button type="submit" size="sm">Add</x-ui.button>
                </form>
                @error('newMenuName')
                    <p class="text-xs text-red-500 mt-1 px-1">{{ $message }}</p>
                @enderror
            </div>
        @else
            <button
                wire:click="$set('showNewMenuForm', true)"
                class="mt-2 w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-molveno-blue-500 hover:bg-molveno-blue-500/5 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                New Menu
            </button>
        @endif
    </div>
</div>
