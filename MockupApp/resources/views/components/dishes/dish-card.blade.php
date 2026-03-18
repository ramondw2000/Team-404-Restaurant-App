@props(['dish', 'allergenConfig'])

<div class="dish-card rounded-2xl shadow-md overflow-hidden flex flex-col
            cursor-pointer select-none transition-all duration-150
            hover:shadow-xl hover:-translate-y-1"
     data-name="{{ strtolower($dish['name']) }}"
     data-category="{{ $dish['category'] }}"
     data-allergens="{{ implode(',', $dish['allergens']) }}"
     data-dietary="{{ implode(',', $dish['dietary']) }}"
     data-price="{{ $dish['price'] }}"
     data-color="{{ $dish['color'] }}"
     onclick="openEditSheet(this)">

    <!-- Image placeholder -->
    <div class="flex-1 flex items-center justify-center"
         style="background-color: {{ $dish['color'] }}">
        <svg class="opacity-30" width="52" height="52" viewBox="0 0 24 24" fill="none"
             stroke="white" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="13" r="8"/>
            <path d="M7 5v3M8 5v3M7.5 8v5"/>
            <path d="M15 5c1 1 1.5 2 1.5 3v6"/>
            <path d="M15 5c-1 1-1.5 2-1.5 3h3"/>
        </svg>
    </div>

    <!-- Info strip -->
    <div class="shrink-0 bg-white px-3 py-2 flex flex-col gap-1">
        <p class="font-bold text-molveno-blue-700 text-xs leading-tight line-clamp-2">{{ $dish['name'] }}</p>
        <p class="text-primary font-black text-xs">&euro;{{ number_format($dish['price'], 2) }}</p>
        <div class="flex items-center gap-1 flex-wrap">
            @foreach($dish['allergens'] as $allergen)
                @if(isset($allergenConfig[$allergen]))
                    <x-dishes.allergen-icon
                        :bg="$allergenConfig[$allergen]['bg']"
                        :icon="$allergenConfig[$allergen]['icon']"
                        :title="$allergenConfig[$allergen]['label']"
                        shadow />
                @endif
            @endforeach
            @if(in_array('vegetarian', $dish['dietary']))
                <div title="Vegetarian" class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center shrink-0 shadow-sm">
                    <svg viewBox="0 0 16 16" width="10" height="10"><path fill="black" d="M3 14c0-5 4-11 10-12C13 7 11 11 8 13l4-3c-1 3-5 5-9 4z"/></svg>
                </div>
            @endif
            @if(in_array('vegan', $dish['dietary']))
                <div title="Vegan" class="w-5 h-5 rounded-full bg-green-700 flex items-center justify-center shrink-0 shadow-sm">
                    <svg viewBox="0 0 16 16" width="10" height="10"><path stroke="black" stroke-width="1.5" fill="none" stroke-linecap="round" d="M8 14V8M8 8C8 5 5 2 2 2C2 5 5 8 8 8M8 8C8 5 11 2 14 2C14 5 11 8 8 8"/></svg>
                </div>
            @endif
        </div>
    </div>
</div>
