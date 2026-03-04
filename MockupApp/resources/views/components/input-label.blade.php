@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm text-white font-bold tracking-widest text-xl']) }}>
    {{ $value ?? $slot }}
</label>
