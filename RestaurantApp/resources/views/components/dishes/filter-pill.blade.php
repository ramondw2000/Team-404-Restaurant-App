@props(['filter', 'value', 'onclick' => null])

<button {{ $attributes->merge([
    'class' => 'filter-btn',
    'data-filter' => $filter,
    'data-value' => $value,
]) }}
    @if($onclick) onclick="{{ $onclick }}" @endif
>
    <span class="inline-flex items-center gap-1">
        {{ $slot }}
    </span>
</button>
