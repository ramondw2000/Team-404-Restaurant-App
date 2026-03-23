@props([
    'src' => null,
    'alt' => '',
    'aspect' => null,
    'placeholderColor' => '#309bcf',
    'rounded' => 'xl',
])

@php
    $aspectStyle = $aspect ? "aspect-ratio: {$aspect};" : '';
    $roundedClass = "rounded-{$rounded}";
@endphp

<div {{ $attributes->merge(['class' => "overflow-hidden {$roundedClass}"]) }}
     @if($aspectStyle) style="{{ $aspectStyle }}" @endif>
    @if($src)
        <img src="{{ $src }}" alt="{{ $alt }}" class="w-full h-full object-cover" draggable="false" />
    @else
        <div class="w-full h-full flex items-center justify-center" style="background-color: {{ $placeholderColor }};">
            @if(isset($placeholderIcon))
                {{ $placeholderIcon }}
            @else
                <svg class="opacity-30 w-12 h-12 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                </svg>
            @endif
        </div>
    @endif
</div>
