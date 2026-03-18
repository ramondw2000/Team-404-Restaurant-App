<div id="review-screen"
     class="fixed inset-0 z-50 bg-[#eaf4fa] flex flex-col"
     style="height: 100dvh;">

    <!-- ── Sticky top nav ─────────────────────────── -->
    <div class="shrink-0 bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between gap-4">
            <button onclick="closeReview()"
                    class="flex items-center gap-2 text-molveno-blue-500 hover:text-molveno-blue-700 font-semibold text-sm transition-colors">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                Back
            </button>
            <h2 class="text-base font-bold text-gray-900 absolute left-1/2 -translate-x-1/2">Your Order</h2>
            <span id="review-nav-table" class="text-xs font-semibold text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full"></span>
        </div>
    </div>

    <!-- ── Scrollable content ─────────────────────── -->
    <div class="flex-1 overflow-y-auto">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 py-6 flex flex-col gap-4">

            <!-- Waiter info row -->
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-molveno-blue-500 flex items-center justify-center shrink-0">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <span class="text-sm text-gray-600 font-medium">John Doe</span>
                <span class="text-gray-300 mx-1">·</span>
                <span id="review-meta-time" class="text-sm text-gray-400"></span>
            </div>

            <!-- ── Order card ── -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div id="review-ticket-header" class="px-4 py-3 text-white"></div>
                <div id="review-dish-list" class="divide-y divide-gray-100"></div>
                <div id="review-card-footer" class="px-4 py-2.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between"></div>
            </div>

            <div class="h-4"></div>
        </div>
    </div>

    <!-- ── Sticky bottom action bar ───────────────── -->
    <div class="shrink-0 bg-white border-t border-gray-200 shadow-[0_-4px_16px_rgba(0,0,0,.06)]">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-4">
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500">Order total</p>
                <p id="review-total" class="text-lg font-black text-gray-900 leading-tight"></p>
            </div>
            <button onclick="sendOrder()"
                    class="shrink-0 flex items-center gap-2 bg-molveno-blue-500 hover:bg-molveno-blue-700
                           text-white text-sm font-bold px-6 py-3 rounded-xl shadow-sm transition-colors">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                Send to Kitchen
            </button>
        </div>
    </div>

</div>
