@props([
    'active' => false,
    'count' => null,
    'value' => null,
])

@php
    $activeClasses = $active
        ? 'bg-molveno-blue-500 border-molveno-blue-500 text-white'
        : 'bg-white border-gray-200 text-gray-600 hover:border-molveno-blue-300 hover:text-molveno-blue-700';

    $countClasses = $active
        ? 'bg-white/25 text-white'
        : 'bg-gray-100 text-gray-500';
@endphp

<button
    type="button"
    @if($value) data-value="{{ $value }}" @endif
    {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border cursor-pointer transition-colors duration-150 shadow-sm {$activeClasses}"]) }}
>
    {{ $slot }}

    @if(!is_null($count))
        <span class="px-1.5 py-0.5 rounded-full text-[0.65rem] font-bold {{ $countClasses }}">
            {{ $count }}
        </span>
    @endif
</button>
