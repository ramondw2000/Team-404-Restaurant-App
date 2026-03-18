@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-white font-bold tracking-widest text-xl']) }}>
    {{ $value ?? $slot }}
</label>
