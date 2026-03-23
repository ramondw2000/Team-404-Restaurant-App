@props([
    'type' => 'text',
    'disabled' => false,
    'error' => false,
])

@php
    $baseClasses = 'w-full border rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 font-[inherit] disabled:opacity-50 disabled:cursor-not-allowed';

    $stateClasses = $error
        ? 'border-red-500 focus:border-red-500 focus:ring-2 focus:ring-red-200'
        : 'border-gray-200 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300';

    $classes = $baseClasses . ' ' . $stateClasses;
@endphp

@if($type === 'textarea')
    <textarea @disabled($disabled) {{ $attributes->merge(['class' => $classes . ' resize-none']) }}>{{ $slot }}</textarea>
@elseif($type === 'select')
    <select @disabled($disabled) {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </select>
@else
    <input type="{{ $type }}" @disabled($disabled) {{ $attributes->merge(['class' => $classes]) }} />
@endif
