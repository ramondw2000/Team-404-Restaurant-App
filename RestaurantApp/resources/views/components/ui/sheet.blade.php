@props([
    'name',
    'side' => 'right',
    'maxWidth' => 'md',
    'title' => null,
    'subtitle' => null,
])

@php
    $maxWidthClass = match ($maxWidth) {
        'sm' => 'sm:max-w-sm',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        default => 'sm:max-w-md',
    };

    $positionClass = $side === 'left' ? 'left-0' : 'right-0';
    $translateFrom = $side === 'left' ? '-translate-x-full' : 'translate-x-full';
@endphp

<div
    x-data="{
        show: false,
        open() {
            this.show = true;
            document.body.style.overflow = 'hidden';
        },
        close() {
            this.show = false;
            document.body.style.overflow = '';
        },
    }"
    x-on:open-sheet.window="if ($event.detail === '{{ $name }}') { open() }"
    x-on:close-sheet.window="if ($event.detail === '{{ $name }}') { close() }"
    x-on:keydown.escape.window="if (show) { close() }"
    {{ $attributes }}
>
    {{-- Overlay --}}
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/40 z-40"
        x-on:click="close()"
        x-cloak
    ></div>

    {{-- Panel --}}
    <div
        x-show="show"
        x-transition:enter="transition ease-[cubic-bezier(0.32,0.72,0,1)] duration-350"
        x-transition:enter-start="{{ $translateFrom }}"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-[cubic-bezier(0.32,0.72,0,1)] duration-350"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="{{ $translateFrom }}"
        x-trap.noscroll="show"
        class="fixed top-0 {{ $positionClass }} z-50 w-full {{ $maxWidthClass }} h-dvh bg-white shadow-2xl flex flex-col"
        x-on:click.stop
        x-cloak
    >
        {{-- Header --}}
        <div class="shrink-0 flex items-start justify-between gap-3 px-5 py-4 border-b border-gray-100">
            <div class="min-w-0">
                @if($title)
                    <h2 class="text-base font-bold text-gray-900 truncate" id="sheet-title-{{ $name }}">{{ $title }}</h2>
                @endif
                @if($subtitle)
                    <p class="text-xs text-gray-500 mt-0.5" id="sheet-subtitle-{{ $name }}">{{ $subtitle }}</p>
                @endif
            </div>
            <button
                x-on:click="close()"
                class="shrink-0 p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                type="button"
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6 6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto px-5 py-4">
            {{ $slot }}
        </div>

        {{-- Footer --}}
        @if(isset($footer))
            <div class="shrink-0 flex items-center justify-end gap-2 px-5 py-4 border-t border-gray-100 bg-white">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
