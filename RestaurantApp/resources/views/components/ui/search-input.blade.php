@props([
    'placeholder' => 'Search...',
    'id' => null,
])

<div {{ $attributes->only('class')->merge(['class' => 'relative']) }}>
    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="8"/>
        <path d="m21 21-4.35-4.35"/>
    </svg>
    <x-ui.input
        type="text"
        :id="$id"
        :placeholder="$placeholder"
        class="pl-10"
        {{ $attributes->except('class') }}
    />
</div>
