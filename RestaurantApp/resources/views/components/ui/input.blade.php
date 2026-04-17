@props([
    'type' => 'text',
    'disabled' => false,
    'error' => false,
])

@php
    $isPassword = $type === 'password';
    $inputId = $attributes->get('id') ?? 'input_' . uniqid();

    $baseClasses = 'w-full border rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 font-[inherit] disabled:opacity-50 disabled:cursor-not-allowed';

    $stateClasses = $error
        ? 'border-red-500 focus:border-red-500 focus:ring-2 focus:ring-red-200'
        : 'border-gray-200 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300';

    $passwordClasses = $isPassword ? ' pr-10' : '';
    $classes = $baseClasses . ' ' . $stateClasses . $passwordClasses;
@endphp

@if($type === 'textarea')
    <textarea @disabled($disabled) {{ $attributes->merge(['class' => $classes . ' resize-none']) }}>{{ $slot }}</textarea>
@elseif($type === 'select')
    <select @disabled($disabled) {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </select>
@elseif($isPassword)
    <div class="relative" x-data="{ show: false }">
        <input
            :type="show ? 'text' : 'password'"
            id="{{ $inputId }}"
            @disabled($disabled)
            {{ $attributes->merge(['class' => $classes])->except(['id', 'type']) }}
        />
        <button
            type="button"
            @click="show = !show"
            class="absolute inset-y-0 right-0 flex items-center justify-center w-10 text-gray-500 hover:text-gray-700 focus:outline-none"
            tabindex="-1"
        >
            <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
            </svg>
        </button>
    </div>
@else
    <input type="{{ $type }}" @disabled($disabled) {{ $attributes->merge(['class' => $classes]) }} />
@endif
