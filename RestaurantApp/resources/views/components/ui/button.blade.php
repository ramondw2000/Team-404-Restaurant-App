@props([
    'variant' => 'primary',
    'size' => 'default',
    'href' => null,
    'disabled' => false,
])

@php
    $variantClasses = match ($variant) {
        'primary'   => 'bg-molveno-blue-500 hover:bg-molveno-blue-700 text-white font-semibold shadow-sm',
        'secondary' => 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 font-medium',
        'danger'    => 'bg-red-600 hover:bg-red-700 text-white font-semibold',
        'ghost'     => 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 font-medium',
        'outline'   => 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold shadow-sm',
        default     => 'bg-molveno-blue-500 hover:bg-molveno-blue-700 text-white font-semibold shadow-sm',
    };

    $sizeClasses = match ($size) {
        'sm'      => 'px-3 py-1.5 text-xs rounded-lg gap-1.5',
        'lg'      => 'px-5 py-2.5 text-base rounded-lg gap-2',
        'pill'    => 'px-5 py-2 text-sm rounded-full gap-1.5',
        default   => 'px-4 py-2 text-sm rounded-lg gap-2',
    };

    $baseClasses = 'inline-flex items-center justify-center transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-molveno-blue-300 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

    $classes = $baseClasses . ' ' . $variantClasses . ' ' . $sizeClasses;

    $tag = $href ? 'a' : 'button';
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button @disabled($disabled) {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
