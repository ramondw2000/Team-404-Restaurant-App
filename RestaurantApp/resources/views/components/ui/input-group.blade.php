@props([
    'label',
    'name',
    'type' => 'text',
    'placeholder' => null,
    'value' => null,
    'required' => false,
    'hint' => null,
    'id' => null,
])

@php
    $inputId = $id ?? $name;
    $inputValue = $value ?? old($name);
    $hasError = isset($errors) && $errors->has($name);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'flex flex-col gap-1.5']) }}>
    <label class="text-sm font-semibold text-gray-700" for="{{ $inputId }}">
        {{ $label }}
        @if($required)
            <span class="text-red-400">*</span>
        @endif
    </label>

    @if($slot->isNotEmpty())
        {{ $slot }}
    @elseif($type === 'select')
        <x-ui.input
            :type="$type"
            :name="$name"
            :id="$inputId"
            :error="$hasError"
        />
    @else
        <x-ui.input
            :type="$type"
            :name="$name"
            :id="$inputId"
            :placeholder="$placeholder"
            :value="$inputValue"
            :error="$hasError"
        />
    @endif

    @if($hint)
        <p class="text-xs text-gray-400">{{ $hint }}</p>
    @endif

    @if($hasError)
        <p class="text-xs text-red-600">{{ $errors->first($name) }}</p>
    @endif
</div>
