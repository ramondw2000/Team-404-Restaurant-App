@props([
    'padding' => 'default',
    'headerColor' => null,
])

@php
    $paddingClasses = match ($padding) {
        'none'    => '',
        'sm'      => 'p-4',
        'lg'      => 'p-8',
        default   => 'p-6',
    };
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden']) }}>
    @if($headerColor && isset($header))
        <div class="px-6 py-4 text-white {{ $headerColor }}">
            {{ $header }}
        </div>
    @elseif(isset($header))
        {{ $header }}
    @endif

    <div @class([$paddingClasses])>
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="border-t border-gray-100 bg-gray-50">
            {{ $footer }}
        </div>
    @endif
</div>
