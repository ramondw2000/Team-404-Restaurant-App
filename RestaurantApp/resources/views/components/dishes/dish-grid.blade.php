@props(['id' => 'dish-grid'])

<div id="{{ $id }}" {{ $attributes->merge(['class' => 'grid gap-4 justify-center dish-grid']) }}>
    {{ $slot }}
</div>
