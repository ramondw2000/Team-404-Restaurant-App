@props(['dish', 'allergenConfig'])

@php
$dotClass = $dish['status'] === 'served' ? 'bg-green-400'
          : ($dish['status'] === 'ready'  ? 'bg-amber-400' : 'bg-gray-300');
@endphp

<div class="px-4 py-3 flex flex-col gap-2">

    <!-- Dish name row -->
    <div class="flex items-start gap-2">
        <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 {{ $dotClass }}"></span>

        <div class="flex-1 min-w-0">
            <div class="flex items-baseline justify-between gap-1">
                <span class="text-sm font-semibold text-gray-800 leading-snug">{{ $dish['name'] }}</span>
                <span class="text-xs text-gray-400 font-medium shrink-0">&times;{{ $dish['qty'] }}</span>
            </div>

            @if(!empty($dish['allergens']))
                <div class="flex items-center gap-1 flex-wrap mt-1">
                    @foreach($dish['allergens'] as $allergen)
                        @if(isset($allergenConfig[$allergen]))
                            <x-dishes.allergen-icon
                                :bg="$allergenConfig[$allergen]['bg']"
                                :icon="$allergenConfig[$allergen]['icon']"
                                :title="$allergenConfig[$allergen]['label']"
                                size="md" />
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Notes textarea -->
    <textarea class="note-area" rows="2"
              placeholder="No notes…">{{ $dish['notes'] }}</textarea>

    <!-- Action buttons -->
    @if($dish['status'] === 'pending')
        <div class="flex gap-1.5">
            <button class="flex-1 inline-flex items-center justify-center gap-1 text-xs font-semibold px-2.5 py-1.5 rounded-lg
                           bg-molveno-blue-500 hover:bg-molveno-blue-700 text-white transition-colors">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                Mark Ready
            </button>
            <button disabled class="flex-1 inline-flex items-center justify-center gap-1 text-xs font-medium px-2.5 py-1.5 rounded-lg
                                    bg-gray-50 border border-gray-200 text-gray-300 cursor-not-allowed">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                Sent Out
            </button>
        </div>
    @elseif($dish['status'] === 'ready')
        <div class="flex gap-1.5">
            <button disabled class="flex-1 inline-flex items-center justify-center gap-1 text-xs font-medium px-2.5 py-1.5 rounded-lg
                                    bg-gray-50 border border-gray-200 text-gray-300 cursor-not-allowed">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                Mark Ready
            </button>
            <button class="flex-1 inline-flex items-center justify-center gap-1 text-xs font-semibold px-2.5 py-1.5 rounded-lg
                           bg-green-600 hover:bg-green-700 text-white transition-colors">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                Sent Out
            </button>
        </div>
    @else
        <div class="inline-flex items-center gap-1 text-xs font-medium text-green-600">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
            Served
        </div>
    @endif

</div>
