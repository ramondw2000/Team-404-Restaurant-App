@props([
    'name',
    'size' => 'default',
    'color' => 'bg-molveno-blue-500',
])

@php
    $parts = explode(' ', $name);
    $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));

    $sizeClasses = match ($size) {
        'sm'    => 'w-7 h-7 text-[0.6rem]',
        'lg'    => 'w-12 h-12 text-sm',
        default => 'w-9 h-9 text-xs',
    };
@endphp

<div {{ $attributes->merge(['class' => "rounded-full flex items-center justify-center shrink-0 text-white font-bold {$color} {$sizeClasses}"]) }}>
    {{ $initials }}
</div>
