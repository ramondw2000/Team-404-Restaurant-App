@props([
    'name',
    'maxWidth' => 'md',
    'closeable' => true,
])

@php
    $maxWidthClass = match ($maxWidth) {
        'sm'  => 'sm:max-w-sm',
        'lg'  => 'sm:max-w-lg',
        'xl'  => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        default => 'sm:max-w-md',
    };
@endphp

<div
    x-data="{
        show: false,
        open() { this.show = true; },
        close() {
            @if($closeable)
                this.show = false;
            @endif
        },
    }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') { open() }"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') { close() }"
    x-on:keydown.escape.window="close()"
    {{ $attributes }}
>
    {{-- Overlay --}}
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/40 z-40"
        @if($closeable) x-on:click="close()" @endif
        x-cloak
    ></div>

    {{-- Panel --}}
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-trap.noscroll="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-cloak
    >
        <div
            class="bg-white rounded-2xl shadow-2xl overflow-hidden w-full {{ $maxWidthClass }}"
            x-on:click.stop
        >
            {{ $slot }}

            @if(isset($footer))
                {{ $footer }}
            @endif
        </div>
    </div>
</div>
