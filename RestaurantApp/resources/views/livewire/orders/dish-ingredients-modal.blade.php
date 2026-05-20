<div>
    <x-modal name="dish-ingredients" maxWidth="md">
        <div class="px-5 py-4 border-b border-gray-100 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Ingredients in</p>
                <h2 class="text-base font-bold text-gray-900 leading-snug truncate">{{ $dishName ?? '—' }}</h2>
            </div>
            <button
                type="button"
                x-on:click="$dispatch('close-modal', 'dish-ingredients')"
                class="w-8 h-8 shrink-0 flex items-center justify-center rounded-full text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors"
                aria-label="Close"
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="px-5 py-4">
            @if(empty($ingredients))
                <p class="text-sm text-gray-500 text-center py-2">No ingredient information available.</p>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach($ingredients as $ingredient)
                        <li class="flex items-center justify-between gap-3 py-2.5">
                            <span @class([
                                'text-sm font-medium',
                                'text-gray-800' => $ingredient['is_available'],
                                'text-gray-400 line-through' => ! $ingredient['is_available'],
                            ])>
                                {{ $ingredient['name'] }}
                            </span>
                            @if($ingredient['is_available'])
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide bg-green-100 text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                    Available
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide bg-red-100 text-red-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    Out of stock
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex justify-end">
            <x-ui.button variant="secondary" x-on:click="$dispatch('close-modal', 'dish-ingredients')">
                Close
            </x-ui.button>
        </div>
    </x-modal>
</div>
