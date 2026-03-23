<div
    x-data="{
        toasts: [],
        maxToasts: 5,
        add(toast) {
            const id = Date.now() + Math.random();
            const duration = toast.duration || 5000;
            this.toasts.push({ id, message: toast.message, type: toast.type || 'info', visible: false });

            if (this.toasts.length > this.maxToasts) {
                this.remove(this.toasts[0].id);
            }

            this.$nextTick(() => {
                const t = this.toasts.find(t => t.id === id);
                if (t) { t.visible = true; }
            });

            setTimeout(() => this.remove(id), duration);
        },
        remove(id) {
            const t = this.toasts.find(t => t.id === id);
            if (t) { t.visible = false; }
            setTimeout(() => {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }, 300);
        },
        typeConfig(type) {
            const config = {
                success: { bg: 'bg-green-50 border-green-200 text-green-800', icon: 'success' },
                error:   { bg: 'bg-red-50 border-red-200 text-red-800', icon: 'error' },
                warning: { bg: 'bg-amber-50 border-amber-200 text-amber-800', icon: 'warning' },
                info:    { bg: 'bg-blue-50 border-blue-200 text-blue-800', icon: 'info' },
            };
            return config[type] || config.info;
        },
    }"
    x-on:toast.window="add($event.detail)"
    x-init="
        @if(session('success'))
            add({ message: '{{ addslashes(session('success')) }}', type: 'success' });
        @endif
        @if(session('error'))
            add({ message: '{{ addslashes(session('error')) }}', type: 'error' });
        @endif
        @if(session('warning'))
            add({ message: '{{ addslashes(session('warning')) }}', type: 'warning' });
        @endif
        @if(session('info'))
            add({ message: '{{ addslashes(session('info')) }}', type: 'info' });
        @endif
    "
    class="fixed bottom-6 right-6 z-[100] flex flex-col gap-3 pointer-events-none"
    aria-live="polite"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="toast.visible"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            :class="typeConfig(toast.type).bg"
            class="pointer-events-auto flex items-center gap-3 border text-sm font-medium px-4 py-3 rounded-xl shadow-lg min-w-[280px] max-w-sm"
        >
            {{-- Success icon --}}
            <template x-if="toast.type === 'success'">
                <svg class="shrink-0 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </template>

            {{-- Error icon --}}
            <template x-if="toast.type === 'error'">
                <svg class="shrink-0 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </template>

            {{-- Warning icon --}}
            <template x-if="toast.type === 'warning'">
                <svg class="shrink-0 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </template>

            {{-- Info icon --}}
            <template x-if="toast.type === 'info'">
                <svg class="shrink-0 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
            </template>

            <span x-text="toast.message" class="flex-1"></span>

            <button
                x-on:click="remove(toast.id)"
                class="shrink-0 ml-auto opacity-60 hover:opacity-100 transition-opacity"
                type="button"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6 6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </template>
</div>
