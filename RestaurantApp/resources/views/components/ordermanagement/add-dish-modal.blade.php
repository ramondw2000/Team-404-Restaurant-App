<div id="add-overlay"
     class="add-overlay fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
     onclick="closeAddModal(event)">

    <div class="add-modal w-full max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col">

        <!-- Header -->
        <div class="px-5 py-4 border-b border-gray-100 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h2 id="add-modal-name" class="text-base font-bold text-gray-900 leading-snug"></h2>
                <p id="add-modal-price" class="text-sm font-semibold text-primary mt-0.5"></p>
            </div>
            <button onclick="closeAddModal()"
                    class="w-8 h-8 shrink-0 flex items-center justify-center rounded-full text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors mt-0.5">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Body -->
        <div class="px-5 py-4 flex flex-col gap-4">

            <!-- Allergen / dietary row -->
            <div id="add-modal-badges" class="flex items-center gap-1.5 flex-wrap"></div>

            <!-- Qty -->
            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-gray-700">Quantity</span>
                <div class="flex items-center gap-2 ms-auto">
                    <button class="qty-btn" onclick="addModalChangeQty(-1)">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M5 12h14"/></svg>
                    </button>
                    <span id="add-modal-qty" class="text-sm font-bold text-gray-800 w-6 text-center">1</span>
                    <button class="qty-btn" onclick="addModalChangeQty(1)">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                    </button>
                </div>
            </div>

            <!-- Note -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Notes for kitchen
                    <span class="text-gray-400 font-normal">(optional)</span>
                </label>
                <textarea id="add-modal-note" class="note-area" rows="3"
                          placeholder="e.g. No onions, extra sauce on the side…"></textarea>
            </div>
        </div>

        <!-- Actions -->
        <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
            <button onclick="closeAddModal()"
                    class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                Cancel
            </button>
            <button onclick="confirmAddDish()"
                    class="flex-1 px-4 py-2.5 rounded-xl bg-molveno-blue-500 hover:bg-molveno-blue-700 text-white text-sm font-bold transition-colors inline-flex items-center justify-center gap-2">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                Add to Order
            </button>
        </div>
    </div>
</div>
