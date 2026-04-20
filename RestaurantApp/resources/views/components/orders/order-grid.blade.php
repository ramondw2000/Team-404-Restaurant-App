@props(['id' => 'order-list'])

<div {{ $attributes->merge(['class' => 'order-grid grid gap-4 items-start xl:grid-cols-4 lg:grid-cols-3 sm:grid-cols-2 grid-cols-1', 'id' => $id]) }}>
    {{ $slot }}
</div>
