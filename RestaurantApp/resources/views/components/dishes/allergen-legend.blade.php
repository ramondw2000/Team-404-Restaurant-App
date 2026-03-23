@props(['allergenConfig'])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-x-4 gap-y-2 sm:justify-end']) }}>
    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide w-full sm:w-auto">Legend:</span>
    @foreach($allergenConfig as $key => $cfg)
        <div class="flex items-center gap-1.5">
            <x-dishes.allergen-icon :bg="$cfg['bg']" :icon="$cfg['icon']" />
            <span class="text-xs font-medium text-gray-600">{{ $cfg['label'] }}</span>
        </div>
    @endforeach
    <div class="flex items-center gap-1.5">
        <x-dishes.dietary-icon type="vegetarian" />
        <span class="text-xs font-medium text-gray-600">Vegetarian</span>
    </div>
    <div class="flex items-center gap-1.5">
        <x-dishes.dietary-icon type="vegan" />
        <span class="text-xs font-medium text-gray-600">Vegan</span>
    </div>
</div>
