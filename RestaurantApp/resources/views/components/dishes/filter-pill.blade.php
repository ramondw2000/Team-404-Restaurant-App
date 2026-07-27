@props(['filter', 'value'])

<x-ui.button
    size="pill"
    variant="outline"
    {{ $attributes->merge([
        'class' => 'filter-btn shadow-none',
        'data-filter' => $filter,
        'data-value' => $value,
    ]) }}
>
    {{ $slot }}
</x-ui.button>
