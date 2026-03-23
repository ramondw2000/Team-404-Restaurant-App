@props(['allergenConfig'])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-x-3 gap-y-2']) }}>
    <span class="text-xs font-semibold text-gray-500 shrink-0">Dietary:</span>
    <x-dishes.filter-pill filter="dietary" value="vegetarian" onclick="toggleMulti(this, 'dietary')">
        <x-dishes.dietary-icon type="vegetarian" size="sm" />
        Vegetarian
    </x-dishes.filter-pill>
    <x-dishes.filter-pill filter="dietary" value="vegan" onclick="toggleMulti(this, 'dietary')">
        <x-dishes.dietary-icon type="vegan" size="sm" />
        Vegan
    </x-dishes.filter-pill>

    <span class="text-gray-300 hidden sm:inline">|</span>
    <span class="text-xs font-semibold text-gray-500 shrink-0">Free from:</span>
    @foreach($allergenConfig as $key => $cfg)
        <x-dishes.filter-pill filter="freefrom" :value="$key" onclick="toggleMulti(this, 'freefrom')">
            <x-dishes.allergen-icon :bg="$cfg['bg']" :icon="$cfg['icon']" size="sm" />
            {{ $cfg['label'] }}-free
        </x-dishes.filter-pill>
    @endforeach
</div>
