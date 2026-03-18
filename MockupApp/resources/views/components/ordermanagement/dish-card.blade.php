@props(['dish', 'allergenConfig'])

<div class="dish-card"
     id="dish-card-{{ $dish['id'] }}"
     data-id="{{ $dish['id'] }}"
     data-cat="{{ $dish['cat'] }}"
     data-name="{{ strtolower($dish['name']) }} {{ strtolower($dish['desc']) }}"
     data-allergens="{{ implode(',', $dish['allergens']) }}"
     data-dietary="{{ implode(',', $dish['dietary']) }}">

    <!-- Text side -->
    <div class="dish-card-body">
        <!-- Name row with dietary icons -->
        <div class="flex items-start gap-2 flex-wrap">
            <span class="text-sm font-bold text-gray-900 leading-snug">{{ $dish['name'] }}</span>
            <div class="flex items-center gap-1 mt-0.5">
                @if(in_array('vegetarian', $dish['dietary']))
                    <div title="Vegetarian" class="w-4 h-4 rounded-full bg-green-500 flex items-center justify-center shrink-0">
                        <svg viewBox="0 0 16 16" width="8" height="8"><path fill="black" d="M3 14c0-5 4-11 10-12C13 7 11 11 8 13l4-3c-1 3-5 5-9 4z"/></svg>
                    </div>
                @endif
                @if(in_array('vegan', $dish['dietary']))
                    <div title="Vegan" class="w-4 h-4 rounded-full bg-green-700 flex items-center justify-center shrink-0">
                        <svg viewBox="0 0 16 16" width="8" height="8"><path stroke="black" stroke-width="1.5" fill="none" stroke-linecap="round" d="M8 14V8M8 8C8 5 5 2 2 2C2 5 5 8 8 8M8 8C8 5 11 2 14 2C14 5 11 8 8 8"/></svg>
                    </div>
                @endif
            </div>
        </div>

        <!-- Price -->
        <p class="text-sm font-semibold text-primary">&euro;&nbsp;{{ number_format($dish['price'], 2) }}</p>

        <!-- Description -->
        <p class="text-xs text-gray-500 leading-snug">{{ $dish['desc'] }}</p>

        <!-- Allergen icons -->
        @if(!empty($dish['allergens']))
            <div class="flex items-center gap-1 flex-wrap mt-1">
                @foreach($dish['allergens'] as $a)
                    @if(isset($allergenConfig[$a]))
                        <x-dishes.allergen-icon
                            :bg="$allergenConfig[$a]['bg']"
                            :icon="$allergenConfig[$a]['icon']"
                            :title="$allergenConfig[$a]['label']"
                            shadow />
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    <!-- Image side (+ button lives HERE, inside this container) -->
    <div class="dish-card-image">
        <!-- Placeholder illustration -->
        <svg class="text-gray-300" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round">
            <circle cx="12" cy="13" r="8"/><path d="M7 5v3M8 5v3M7.5 8v5"/>
            <path d="M15 5c1 1 1.5 2 1.5 3v6"/><path d="M15 5c-1 1-1.5 2-1.5 3h3"/>
        </svg>

        <!-- Qty badge (top-right of image) -->
        <div class="qty-badge" id="badge-{{ $dish['id'] }}">0</div>

        <!-- Add button (bottom-right of image) -->
        <button class="btn-add-dish"
                onclick="addDish({{ $dish['id'] }})"
                aria-label="Add {{ $dish['name'] }}">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
        </button>
    </div>
</div>
