@props(['status'])

@php
    $classes = match ($status) {
        'scheduled'  => 'bg-blue-100 text-blue-700',
        'arrived'    => 'bg-green-100 text-green-700',
        'departed'   => 'bg-gray-100 text-gray-600',
        'cancelled'  => 'bg-red-100 text-red-700',
        'late'       => 'bg-amber-100 text-amber-700',
        'optional'   => 'bg-purple-100 text-purple-700',
        'no_show'    => 'bg-rose-100 text-rose-700',
        default      => 'bg-gray-100 text-gray-600',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {$classes}"]) }}>
    {{ ucfirst(str_replace('_', ' ', $status)) }}
</span>
