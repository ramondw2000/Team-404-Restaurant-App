@props(['id' => 'dish-grid'])

<div id="{{ $id }}" {{ $attributes->merge(['class' => 'grid grid-cols-1 sm:grid-cols-2 gap-3']) }}>
    {{ $slot }}
</div>
