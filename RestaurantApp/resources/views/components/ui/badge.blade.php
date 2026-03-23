@props([
    'variant' => 'neutral',
    'size' => 'default',
    'dot' => false,
    'dotColor' => null,
])

@php
    $variantClasses = match ($variant) {
        'primary' => 'bg-molveno-blue-100 text-molveno-blue-700',
        'success' => 'bg-green-100 text-green-700',
        'warning' => 'bg-amber-100 text-amber-700',
        'danger'  => 'bg-red-100 text-red-700',
        'custom'  => '',
        default   => 'bg-gray-100 text-gray-600',
    };

    $sizeClasses = match ($size) {
        'sm'    => 'px-2 py-0.5 text-[0.65rem]',
        default => 'px-2.5 py-1 text-xs',
    };

    $dotClasses = $dotColor ?? match ($variant) {
        'primary' => 'bg-molveno-blue-500',
        'success' => 'bg-green-500',
        'warning' => 'bg-amber-500',
        'danger'  => 'bg-red-500',
        'custom'  => 'bg-gray-400',
        default   => 'bg-gray-400',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full font-semibold {$variantClasses} {$sizeClasses}"]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotClasses }}"></span>
    @endif
    {{ $slot }}
</span>
