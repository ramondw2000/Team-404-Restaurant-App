<!-- ── Sheet overlay ───────────────────────────────────── -->
<div id="sheet-overlay"
     class="sheet-overlay fixed inset-0 z-40 bg-black/30 backdrop-blur-md"
     onclick="closeSheet()">
</div>

<!-- ── Sheet panel ─────────────────────────────────────── -->
<div id="sheet-panel"
     class="sheet-panel fixed top-0 right-0 z-50 w-full sm:max-w-md bg-white shadow-2xl flex flex-col">

    <!-- Header -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
        <div>
            <h2 id="sheet-title" class="text-base font-bold text-gray-900">Add New Dish</h2>
            <p id="sheet-subtitle" class="text-xs text-gray-400 mt-0.5">Fill in the details below to add a dish to the menu.</p>
        </div>
        <button onclick="closeSheet()"
                class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2.5" stroke-linecap="round">
                <path d="M18 6 6 18M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Body -->
    <form id="dish-form" method="POST" action="{{ route('dishes.store') }}" enctype="multipart/form-data"
          class="flex-1 overflow-y-auto px-6 py-5 flex flex-col gap-5">
        @csrf
        <input type="hidden" id="dish-color" name="color" value="#309bcf">

        <input type="file" id="dish-photo" name="photo" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden">

        <!-- Edit mode: current photo preview -->
        <div id="current-photo-preview" class="hidden">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Photo</label>
            <div class="relative w-full rounded-xl overflow-hidden group cursor-pointer" style="aspect-ratio:16/9"
                 onclick="document.getElementById('dish-photo').click()">
                <div id="preview-bg" class="w-full h-full flex items-center justify-center">
                    <img id="current-photo-img" class="hidden absolute inset-0 w-full h-full object-cover" alt="">
                    <svg id="preview-placeholder" class="opacity-30" width="52" height="52" viewBox="0 0 24 24" fill="none"
                         stroke="white" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="13" r="8"/>
                        <path d="M7 5v3M8 5v3M7.5 8v5"/>
                        <path d="M15 5c1 1 1.5 2 1.5 3v6"/>
                        <path d="M15 5c-1 1-1.5 2-1.5 3h3"/>
                    </svg>
                </div>
                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                        <circle cx="12" cy="13" r="4"/>
                    </svg>
                    <span class="text-white text-sm font-semibold">Change photo</span>
                </div>
            </div>
        </div>

        <!-- Create mode: upload zone -->
        <div id="upload-zone-wrapper">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Photo</label>
            <div class="upload-zone" onclick="document.getElementById('dish-photo').click()">
                <img id="upload-preview-img" class="hidden w-full rounded-lg object-cover mb-2" style="max-height:160px" alt="Preview">
                <div id="upload-placeholder">
                    <svg class="mx-auto mb-2 text-gray-300" width="36" height="36" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                        <rect x="3" y="3" width="18" height="18" rx="3"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <path d="M21 15l-5-5L5 21"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-500">Click to upload or drag &amp; drop</p>
                    <p class="text-xs text-gray-400 mt-0.5">PNG, JPG up to 5 MB</p>
                </div>
            </div>
        </div>

        <div>
            <label for="dish-name" class="block text-sm font-semibold text-gray-700 mb-1.5">
                Dish Name <span class="text-red-400">*</span>
            </label>
            <input id="dish-name" name="name" type="text" class="sheet-input" placeholder="e.g. Spaghetti Bolognese"/>
        </div>

        <div>
            <label for="dish-desc" class="block text-sm font-semibold text-gray-700 mb-1.5">Description</label>
            <textarea id="dish-desc" name="description" rows="3" class="sheet-input resize-none"
                      placeholder="Short description of the dish…"></textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="dish-price" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Price <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400 font-medium pointer-events-none">&euro;</span>
                    <input id="dish-price" name="price" type="number" min="0" step="0.01" class="sheet-input !pl-7" placeholder="0.00"/>
                </div>
            </div>
            <div>
                <label for="dish-category" class="block text-sm font-semibold text-gray-700 mb-1.5">Category</label>
                <select id="dish-category" name="category" class="sheet-input">
                    <option value="" disabled selected>Select…</option>
                    <option>Starters</option>
                    <option>Mains</option>
                    <option>Desserts</option>
                    <option>Drinks</option>
                    <option>Sides</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Allergens</label>
            <p class="text-xs text-gray-400 mb-2.5">Select all that apply.</p>
            <div class="flex flex-wrap gap-2">
                <div>
                    <input type="checkbox" id="al-gluten" name="allergens[]" value="gluten" class="allergen-checkbox"/>
                    <label for="al-gluten" class="allergen-label">
                        <span class="w-4 h-4 rounded-full flex items-center justify-center shrink-0" style="background:#D97706">
                            <svg viewBox="0 0 16 16" width="9" height="9"><path fill="white" d="M8 1.5C6.5 3 5 5.5 5 7.5c0 1 .4 1.9 1 2.6V14h4V10.1c.6-.7 1-1.6 1-2.6 0-2-1.5-4.5-3-6z"/></svg>
                        </span>
                        Gluten
                    </label>
                </div>
                <div>
                    <input type="checkbox" id="al-nuts" name="allergens[]" value="nuts" class="allergen-checkbox"/>
                    <label for="al-nuts" class="allergen-label">
                        <span class="w-4 h-4 rounded-full flex items-center justify-center shrink-0" style="background:#92400E">
                            <svg viewBox="0 0 16 16" width="9" height="9"><ellipse cx="8" cy="9.5" rx="5" ry="5.5" fill="white"/><path d="M5.5 5C5.5 3.3 6.6 2 8 2s2.5 1.3 2.5 3" stroke="#92400E" stroke-width="1" fill="none" stroke-linecap="round"/></svg>
                        </span>
                        Nuts
                    </label>
                </div>
                <div>
                    <input type="checkbox" id="al-milk" name="allergens[]" value="milk" class="allergen-checkbox"/>
                    <label for="al-milk" class="allergen-label">
                        <span class="w-4 h-4 rounded-full flex items-center justify-center shrink-0" style="background:#0284C7">
                            <svg viewBox="0 0 16 16" width="9" height="9"><path fill="white" d="M6 2h4l.5 2.5H5.5L6 2zM5 5h6l-1 9H6L5 5z"/></svg>
                        </span>
                        Milk
                    </label>
                </div>
                <div>
                    <input type="checkbox" id="al-wheat" name="allergens[]" value="wheat" class="allergen-checkbox"/>
                    <label for="al-wheat" class="allergen-label">
                        <span class="w-4 h-4 rounded-full flex items-center justify-center shrink-0" style="background:#CA8A04">
                            <svg viewBox="0 0 16 16" width="9" height="9"><line x1="8" y1="14" x2="8" y2="4" stroke="white" stroke-width="1.5"/><ellipse cx="5.5" cy="6" rx="2.5" ry="1.5" fill="white" transform="rotate(-20 5.5 6)"/><ellipse cx="10.5" cy="6" rx="2.5" ry="1.5" fill="white" transform="rotate(20 10.5 6)"/><ellipse cx="5" cy="9" rx="2.5" ry="1.5" fill="white" transform="rotate(-20 5 9)"/><ellipse cx="11" cy="9" rx="2.5" ry="1.5" fill="white" transform="rotate(20 11 9)"/><ellipse cx="8" cy="3" rx="1.5" ry="2" fill="white"/></svg>
                        </span>
                        Wheat
                    </label>
                </div>
                <div>
                    <input type="checkbox" id="al-fish" name="allergens[]" value="fish" class="allergen-checkbox"/>
                    <label for="al-fish" class="allergen-label">
                        <span class="w-4 h-4 rounded-full flex items-center justify-center shrink-0" style="background:#0891B2">
                            <svg viewBox="0 0 16 16" width="9" height="9"><path fill="white" d="M2 8c2-3 5-4 8-4s6 1 8 4c-2 3-5 4-8 4S4 11 2 8z"/><circle cx="13" cy="8" r="1.2" fill="#0891B2"/></svg>
                        </span>
                        Fish
                    </label>
                </div>
                <div>
                    <input type="checkbox" id="al-egg" name="allergens[]" value="egg" class="allergen-checkbox"/>
                    <label for="al-egg" class="allergen-label">
                        <span class="w-4 h-4 rounded-full flex items-center justify-center shrink-0" style="background:#7C3AED">
                            <svg viewBox="0 0 16 16" width="9" height="9"><ellipse cx="8" cy="9" rx="5" ry="6" fill="white"/><ellipse cx="8" cy="10" rx="2.5" ry="3" fill="#7C3AED"/></svg>
                        </span>
                        Egg
                    </label>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Dietary</label>
            <p class="text-xs text-gray-400 mb-2.5">Select all that apply.</p>
            <div class="flex flex-wrap gap-2">
                <div>
                    <input type="checkbox" id="diet-veg" name="dietary[]" value="vegetarian" class="allergen-checkbox"/>
                    <label for="diet-veg" class="allergen-label">
                        <span class="w-4 h-4 rounded-full bg-green-500 flex items-center justify-center shrink-0">
                            <svg viewBox="0 0 16 16" width="9" height="9"><path fill="black" d="M3 14c0-5 4-11 10-12C13 7 11 11 8 13l4-3c-1 3-5 5-9 4z"/></svg>
                        </span>
                        Vegetarian
                    </label>
                </div>
                <div>
                    <input type="checkbox" id="diet-vegan" name="dietary[]" value="vegan" class="allergen-checkbox"/>
                    <label for="diet-vegan" class="allergen-label">
                        <span class="w-4 h-4 rounded-full bg-green-700 flex items-center justify-center shrink-0">
                            <svg viewBox="0 0 16 16" width="9" height="9"><path stroke="black" stroke-width="1.5" fill="none" stroke-linecap="round" d="M8 14V8M8 8C8 5 5 2 2 2C2 5 5 8 8 8M8 8C8 5 11 2 14 2C14 5 11 8 8 8"/></svg>
                        </span>
                        Vegan
                    </label>
                </div>
            </div>
        </div>

    </form>

    <!-- Footer -->
    <div class="shrink-0 border-t border-gray-100 px-6 py-4 flex items-center gap-3 bg-gray-50">
        <form id="delete-dish-form" method="POST" class="hidden mr-auto">
            @csrf
            @method('DELETE')
            <x-ui.button variant="danger" type="submit" id="sheet-delete-btn" size="sm"
                         onclick="return confirm('Are you sure you want to delete this dish?')">
                Delete Dish
            </x-ui.button>
        </form>
        <x-ui.button variant="secondary" onclick="closeSheet()" class="ml-auto" size="sm">
            Cancel
        </x-ui.button>
        <x-ui.button type="submit" form="dish-form" id="sheet-save-btn" size="sm">
            Save Dish
        </x-ui.button>
    </div>
</div>
