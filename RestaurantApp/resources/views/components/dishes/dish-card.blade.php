@props(['dish', 'allergenConfig'])

<div class="dish-card rounded-2xl shadow-md overflow-hidden flex flex-col
            cursor-pointer select-none transition-all duration-150
            hover:shadow-xl hover:-translate-y-1 {{ $dish['is_available'] ? '' : 'opacity-60' }}"
     data-id="{{ $dish['id'] }}"
     data-name="{{ strtolower($dish['name']) }}"
     data-description="{{ $dish['description'] ?? '' }}"
     data-category="{{ $dish['category'] }}"
     data-allergens="{{ implode(',', $dish['allergens']) }}"
     data-dietary="{{ implode(',', $dish['dietary']) }}"
     data-price="{{ $dish['price'] }}"
     data-color="{{ $dish['color'] }}"
     data-photo="{{ $dish['photo_path'] ? asset('storage/' . $dish['photo_path']) : '' }}"
     data-available="{{ $dish['is_available'] ? '1' : '0' }}"
     onclick="openEditSheet(this)">

    <!-- Image / placeholder -->
    <div class="flex-1 flex items-center justify-center overflow-hidden"
         style="background-color: {{ $dish['color'] }}">
        @if($dish['photo_path'])
            <img src="{{ asset('storage/' . $dish['photo_path']) }}"
                 alt="{{ $dish['name'] }}"
                 class="w-full h-full object-cover">
        @else
            <svg class="opacity-30" width="52" height="52" viewBox="0 0 24 24" fill="none"
                 stroke="white" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="13" r="8"/>
                <path d="M7 5v3M8 5v3M7.5 8v5"/>
                <path d="M15 5c1 1 1.5 2 1.5 3v6"/>
                <path d="M15 5c-1 1-1.5 2-1.5 3h3"/>
            </svg>
        @endif
    </div>

    <!-- Info strip -->
    <div class="shrink-0 bg-white px-3 py-2 flex flex-col gap-1">
        <div class="flex items-start justify-between gap-1">
            <p class="font-bold text-molveno-blue-700 text-xs leading-tight line-clamp-2">{{ $dish['name'] }}</p>
            @if(!$dish['is_available'])
                <span class="shrink-0 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-200 text-gray-600">
                    Unavailable
                </span>
            @endif
        </div>
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
