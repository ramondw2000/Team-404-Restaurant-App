<!-- ── Hidden delete form ─────────────────────────────────── -->
<x-ui.form id="delete-form" method="DELETE" class="hidden" />

<!-- ── Delete overlay ──────────────────────────────────────── -->
<div id="delete-overlay"
     class="sheet-overlay fixed inset-0 bg-black/40 z-40 flex items-center justify-center"
     onclick="closeDelete()">
</div>

<!-- ── Delete confirmation modal ──────────────────────────── -->
<div id="delete-modal"
     class="fixed z-50 bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm mx-4
            top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2
            transition-all duration-200 scale-95 opacity-0 pointer-events-none">
    <div class="flex items-start gap-3 mb-4">
        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                <path d="M10 11v6M14 11v6"/>
                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
            </svg>
        </div>
        <div>
            <h3 class="text-sm font-bold text-gray-900">Delete Account</h3>
            <p id="delete-msg" class="text-sm text-gray-500 mt-1"></p>
        </div>
    </div>
    <div class="flex justify-end gap-2">
        <x-ui.button type="button" variant="secondary" size="sm" onclick="closeDelete()">
            Cancel
        </x-ui.button>
        <x-ui.button type="button" variant="danger" size="sm" id="delete-confirm-btn">
            Delete
        </x-ui.button>
    </div>
</div>
