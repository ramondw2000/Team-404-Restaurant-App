@props(['filter', 'value', 'onclick' => null])

<x-ui.button
    size="pill"
    variant="outline"
    {{ $attributes->merge([
        'class' => 'filter-btn shadow-none',
        'data-filter' => $filter,
        'data-value' => $value,
        'onclick' => $onclick,
    ]) }}
>
    {{ $slot }}
</x-ui.button>
