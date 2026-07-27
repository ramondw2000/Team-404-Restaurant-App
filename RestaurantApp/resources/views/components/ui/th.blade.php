@props([
    'align' => 'left',
    'sortable' => false,
    'sorted' => null,
])

@php
    $alignClass = match ($align) {
        'center' => 'text-center',
        'right'  => 'text-right',
        default  => 'text-left',
    };
@endphp

<th {{ $attributes->merge(['class' => "px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide {$alignClass}" . ($sortable ? ' cursor-pointer hover:text-gray-700 select-none' : '')]) }}>
    @if($sortable)
        <span class="inline-flex items-center gap-1">
            {{ $slot }}
            @if($sorted === 'asc')
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
            @elseif($sorted === 'desc')
                <svg class="w-3 h-3 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
            @endif
        </span>
    @else
        {{ $slot }}
    @endif
</th>
