{{-- Global confirmation modal — replaces browser confirm() dialogs --}}
<div
    x-data="{
        show: false,
        title: '',
        message: '',
        confirmLabel: 'Confirm',
        cancelLabel: 'Cancel',
        variant: 'danger',
        onConfirm: null,

        open({ title, message, confirmLabel, cancelLabel, variant, onConfirm }) {
            this.title = title || 'Are you sure?';
            this.message = message || '';
            this.confirmLabel = confirmLabel || 'Confirm';
            this.cancelLabel = cancelLabel || 'Cancel';
            this.variant = variant || 'danger';
            this.onConfirm = onConfirm || null;
            this.show = true;
        },

        close() {
            this.show = false;
        },

        confirm() {
            if (this.onConfirm) {
                this.onConfirm();
            }
            this.close();
        },
    }"
    x-on:confirm-modal.window="open($event.detail)"
    x-on:keydown.escape.window="if (show) close()"
    id="global-confirm-modal"
>
    {{-- Overlay --}}
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/40 z-[60]"
        x-on:click="close()"
        x-cloak
    ></div>

    {{-- Panel --}}
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-[61] flex items-center justify-center p-4"
        x-cloak
    >
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden w-full max-w-sm" x-on:click.stop>
            <div class="p-6">
                <div class="flex items-start gap-3">
                    {{-- Icon --}}
                    <div
                        class="w-10 h-10 rounded-full flex items-center justify-center shrink-0"
                        :class="variant === 'danger' ? 'bg-red-100' : 'bg-amber-100'"
                    >
                        <svg
                            x-show="variant === 'danger'"
                            width="18" height="18" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            :stroke="variant === 'danger' ? '#dc2626' : '#d97706'"
                        >
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                            <path d="M10 11v6M14 11v6"/>
                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                        </svg>
                        <svg
                            x-show="variant !== 'danger'"
                            width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        >
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                    </div>

                    {{-- Text --}}
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-bold text-gray-900" x-text="title"></h3>
                        <p class="text-sm text-gray-500 mt-1" x-text="message"></p>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex justify-end gap-2 px-6 pb-5">
                <button
                    x-on:click="close()"
                    type="button"
                    class="inline-flex items-center justify-center px-3 py-1.5 text-xs rounded-lg font-medium bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-molveno-blue-300 focus:ring-offset-2"
                    x-text="cancelLabel"
                ></button>
                <button
                    x-on:click="confirm()"
                    type="button"
                    class="inline-flex items-center justify-center px-3 py-1.5 text-xs rounded-lg font-semibold transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-molveno-blue-300 focus:ring-offset-2"
                    :class="variant === 'danger' ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-amber-500 hover:bg-amber-600 text-white'"
                    x-text="confirmLabel"
                ></button>
            </div>
        </div>
    </div>
</div>
