@props([
    'title' => null,
    'subtitle' => null,
])

<aside
    x-data="{ mobileOpen: false }"
    {{ $attributes }}
>
    {{-- Mobile overlay --}}
    <div
        x-show="mobileOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-black/40 md:hidden"
        x-cloak
        @click="mobileOpen = false"
    ></div>

    {{-- Sidebar panel --}}
    <div
        class="
            fixed top-0 left-0 z-50 w-72 h-dvh bg-white shadow-2xl flex flex-col
            transition-transform duration-300 ease-[cubic-bezier(0.32,0.72,0,1)]
            md:relative md:h-full md:w-full md:shadow-none md:translate-x-0 md:z-auto
        "
        :class="mobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
        @click.stop
    >
        {{-- Header --}}
        @if($title || $subtitle)
            <div class="shrink-0 flex items-start justify-between gap-3 px-4 pt-4 pb-3 border-b border-gray-100">
                <div class="min-w-0">
                    @if($title)
                        <h2 class="text-sm font-bold text-gray-900 truncate">{{ $title }}</h2>
                    @endif
                    @if($subtitle)
                        <p class="text-xs text-gray-500 mt-0.5">{{ $subtitle }}</p>
                    @endif
                </div>
                <button
                    type="button"
                    class="md:hidden shrink-0 p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                    @click="mobileOpen = false"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        {{-- Scrollable content area --}}
        <div class="flex-1 overflow-y-auto">
            {{ $slot }}
        </div>
    </div>

    {{-- Mobile trigger button (exposed as slot for parent to use) --}}
    @if(isset($trigger))
        <div class="md:hidden">
            {{ $trigger }}
        </div>
    @endif
</aside>
