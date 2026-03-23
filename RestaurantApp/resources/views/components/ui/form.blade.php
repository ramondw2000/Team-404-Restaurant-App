@props([
    'method' => 'POST',
    'action' => '',
])

@php
    $uppercaseMethod = strtoupper($method);
    $formMethod = in_array($uppercaseMethod, ['GET', 'POST']) ? $uppercaseMethod : 'POST';
    $needsSpoofing = ! in_array($uppercaseMethod, ['GET', 'POST']);
@endphp

<form method="{{ $formMethod }}" action="{{ $action }}" {{ $attributes }}>
    @if($uppercaseMethod !== 'GET')
        @csrf
    @endif

    @if($needsSpoofing)
        @method($uppercaseMethod)
    @endif

    {{ $slot }}
</form>
