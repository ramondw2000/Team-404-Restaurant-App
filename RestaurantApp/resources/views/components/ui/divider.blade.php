@props([
    'orientation' => 'horizontal',
    'dashed' => false,
])

@if($orientation === 'vertical')
    @if($dashed)
        <div {{ $attributes->merge(['class' => 'border-l border-dashed border-gray-200']) }}></div>
    @else
        <div {{ $attributes->merge(['class' => 'w-px bg-gray-200']) }}></div>
    @endif
@else
    <div {{ $attributes->merge(['class' => 'w-full border-t border-gray-200' . ($dashed ? ' border-dashed' : '')]) }}></div>
@endif
