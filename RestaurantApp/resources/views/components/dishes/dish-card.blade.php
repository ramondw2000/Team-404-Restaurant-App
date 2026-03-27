@props(['dish', 'allergenConfig'])

<div class="dish-card rounded-2xl shadow-md overflow-hidden flex flex-col
            cursor-pointer select-none transition-all duration-150
            hover:shadow-xl hover:-translate-y-1"
     data-id="{{ $dish['id'] }}"
     data-name="{{ strtolower($dish['name']) }}"
     data-description="{{ $dish['description'] ?? '' }}"
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
                <x-dishes.dietary-icon type="vegetarian" shadow />
            @endif
            @if(in_array('vegan', $dish['dietary']))
                <x-dishes.dietary-icon type="vegan" shadow />
            @endif
        </div>
    </div>
</div>
