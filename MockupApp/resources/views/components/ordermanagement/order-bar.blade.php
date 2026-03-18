<div id="order-bar"
     class="hidden-bar fixed bottom-0 left-0 right-0 z-30 bg-white border-t border-gray-200 shadow-2xl px-4 sm:px-6 py-3">
    <div class="max-w-screen-xl mx-auto flex items-center justify-between gap-4">
        <div class="flex items-center gap-4 min-w-0">
            <div class="w-9 h-9 rounded-full bg-molveno-blue-500 flex items-center justify-center shrink-0 shadow-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-bold text-gray-800 truncate">
                    <span id="bar-count">0</span> <span id="bar-item-label">items</span>
                    <span class="text-gray-400 font-normal mx-1">&middot;</span>
                    <span id="bar-table" class="text-gray-500 font-normal">No table</span>
                </p>
                <p class="text-xs text-gray-400">John Doe &middot; <span id="bar-total" class="font-semibold text-gray-600">&euro; 0.00</span></p>
            </div>
        </div>
        <button onclick="openReview()"
                class="shrink-0 flex items-center gap-2 bg-molveno-blue-500 hover:bg-molveno-blue-700
                       text-white text-sm font-bold px-5 py-2.5 rounded-xl shadow-sm transition-colors">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
            Review Order
        </button>
    </div>
</div>
