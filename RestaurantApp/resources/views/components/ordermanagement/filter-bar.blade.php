@props(['allergenConfig'])

<div class="flex flex-wrap items-center gap-x-3 gap-y-2">
    <span class="text-xs font-semibold text-gray-500 shrink-0">Dietary:</span>

    <button class="filter-btn" data-dietary="vegetarian" onclick="toggleDietary(this)">
        <span class="inline-flex items-center gap-1.5">
            <span class="w-3.5 h-3.5 rounded-full bg-green-500 diet-icon-veg inline-flex items-center justify-center">
                <svg viewBox="0 0 16 16" width="8" height="8"><path fill="black" d="M3 14c0-5 4-11 10-12C13 7 11 11 8 13l4-3c-1 3-5 5-9 4z"/></svg>
            </span>
            Vegetarian
        </span>
    </button>

    <button class="filter-btn" data-dietary="vegan" onclick="toggleDietary(this)">
        <span class="inline-flex items-center gap-1.5">
            <span class="w-3.5 h-3.5 rounded-full bg-green-700 diet-icon-vegan inline-flex items-center justify-center">
                <svg viewBox="0 0 16 16" width="8" height="8"><path stroke="black" stroke-width="1.5" fill="none" stroke-linecap="round" d="M8 14V8M8 8C8 5 5 2 2 2C2 5 5 8 8 8M8 8C8 5 11 2 14 2C14 5 11 8 8 8"/></svg>
            </span>
            Vegan
        </span>
    </button>

    <span class="text-gray-300 hidden sm:inline">|</span>
    <span class="text-xs font-semibold text-gray-500 shrink-0">Free from:</span>

    @foreach($allergenConfig as $key => $cfg)
        <button class="filter-btn" data-freefrom="{{ $key }}" onclick="toggleFreefrom(this)">
            <span class="inline-flex items-center gap-1.5">
                <x-dishes.allergen-icon :bg="$cfg['bg']" :icon="$cfg['icon']" size="sm" />
                {{ $cfg['label'] }}-free
            </span>
        </button>
    @endforeach
</div>
