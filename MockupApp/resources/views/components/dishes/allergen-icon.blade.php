@props(['bg', 'icon', 'title' => null, 'size' => 'lg', 'shadow' => false])

@php
$sizeMap = [
    'sm' => ['container' => 'w-3.5 h-3.5', 'svg' => 8],
    'md' => ['container' => 'w-4 h-4',     'svg' => 9],
    'lg' => ['container' => 'w-5 h-5',     'svg' => 11],
];
$s = $sizeMap[$size];
@endphp

<div class="{{ $s['container'] }} rounded-full flex items-center justify-center shrink-0{{ $shadow ? ' shadow-sm' : '' }}"
     style="background-color: {{ $bg }}"
     @if($title) title="{{ $title }}" @endif>
    <svg viewBox="0 0 16 16" width="{{ $s['svg'] }}" height="{{ $s['svg'] }}">{!! $icon !!}</svg>
</div>
