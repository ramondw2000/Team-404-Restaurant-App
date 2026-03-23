@props(['type', 'size' => 'lg', 'shadow' => false])

@php
$config = match ($type) {
    'vegetarian' => [
        'bg'    => 'bg-green-500',
        'label' => 'Vegetarian',
        'svg'   => '<path fill="black" d="M3 14c0-5 4-11 10-12C13 7 11 11 8 13l4-3c-1 3-5 5-9 4z"/>',
    ],
    'vegan' => [
        'bg'    => 'bg-green-700',
        'label' => 'Vegan',
        'svg'   => '<path stroke="black" stroke-width="1.5" fill="none" stroke-linecap="round" d="M8 14V8M8 8C8 5 5 2 2 2C2 5 5 8 8 8M8 8C8 5 11 2 14 2C14 5 11 8 8 8"/>',
    ],
    default => null,
};

$sizeMap = [
    'sm' => ['container' => 'w-3.5 h-3.5', 'svg' => 8],
    'md' => ['container' => 'w-4 h-4',     'svg' => 9],
    'lg' => ['container' => 'w-5 h-5',     'svg' => 11],
];
$s = $sizeMap[$size] ?? $sizeMap['lg'];
@endphp

@if($config)
<div {{ $attributes->merge(['class' => "{$s['container']} rounded-full {$config['bg']} flex items-center justify-center shrink-0" . ($shadow ? ' shadow-sm' : '')]) }}
     title="{{ $config['label'] }}">
    <svg viewBox="0 0 16 16" width="{{ $s['svg'] }}" height="{{ $s['svg'] }}">{!! $config['svg'] !!}</svg>
</div>
@endif
