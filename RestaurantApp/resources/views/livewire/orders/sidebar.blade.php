<div class="flex flex-col h-full">

    {{-- Menu list --}}
    <div class="flex-1 overflow-y-auto px-3 py-4">
        <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-2 mb-2">Menus</h3>

        @if($this->menus->isEmpty())
            <p class="text-xs text-gray-400 px-2">No published menus available.</p>
        @else
            <nav class="flex flex-col gap-0.5">
                @foreach($this->menus as $menu)
                    <div wire:key="sidebar-menu-{{ $menu->id }}">
                        {{-- Menu row --}}
                        <div class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors cursor-pointer
                            {{ $activeMenuId === $menu->id && !$activeCategoryId ? 'bg-molveno-blue-500 text-white' : 'text-gray-700 hover:bg-gray-50' }}">

                            <span
                                wire:click.stop="toggleMenu({{ $menu->id }})"
                                class="shrink-0 w-4 h-4 flex items-center justify-center cursor-pointer"
                            >
                                <svg class="w-3 h-3 transition-transform {{ ($expandedMenus[$menu->id] ?? false) ? 'rotate-90' : '' }}"
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/>
                                </svg>
                            </span>

                            <span wire:click="selectMenu({{ $menu->id }})" class="truncate flex-1">{{ $menu->name }}</span>
                        </div>

                        {{-- Categories (expandable) --}}
                        @if($expandedMenus[$menu->id] ?? false)
                            <div class="ml-6 flex flex-col gap-0.5 mt-0.5">
                                @foreach($menu->categories as $category)
                                    <button
                                        wire:key="sidebar-cat-{{ $category->id }}"
                                        wire:click="selectCategory({{ $menu->id }}, {{ $category->id }})"
                                        class="w-full flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-medium transition-colors text-left
                                            {{ $activeCategoryId === $category->id ? 'bg-molveno-blue-100/50 text-molveno-blue-700' : 'text-gray-600 hover:bg-gray-50' }}"
                                    >
                                        <span class="truncate">{{ $category->name }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </nav>
        @endif
    </div>

    {{-- Back link --}}
    <div class="shrink-0 px-3 py-4 border-t border-gray-100">
        <a
            href="{{ route('tablemanagement') }}"
            class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors"
        >
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5M12 5l-7 7 7 7"/>
            </svg>
            Back to Table Management
        </a>
    </div>
</div>
