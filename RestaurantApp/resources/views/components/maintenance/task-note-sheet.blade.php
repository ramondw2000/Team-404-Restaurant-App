@php
    $baseUrl = url('maintenance');
@endphp

<div
    x-data="{
        show: false,
        taskId: null,
        taskName: '',
        notes: '',
        get formAction() {
            return '{{ $baseUrl }}/' + this.taskId + '/notes';
        },
        open(id, taskName, notes) {
            this.taskId   = id;
            this.taskName = taskName;
            this.notes    = notes;
            this.show     = true;
            document.body.style.overflow = 'hidden';
        },
        close() {
            this.show = false;
            document.body.style.overflow = '';
        },
    }"
    @open-notes-sheet.window="open($event.detail.id, $event.detail.taskName, $event.detail.notes)"
    @keydown.escape.window="if (show) close()"
>
    {{-- Overlay --}}
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/40 z-40"
        @click="close()"
        x-cloak
    ></div>

    {{-- Panel --}}
    <div
        x-show="show"
        x-transition:enter="transition ease-[cubic-bezier(0.32,0.72,0,1)] duration-350"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-[cubic-bezier(0.32,0.72,0,1)] duration-350"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        x-trap.noscroll="show"
        class="fixed top-0 right-0 z-50 w-full sm:max-w-md h-dvh bg-white shadow-2xl flex flex-col"
        @click.stop
        x-cloak
    >
        {{-- Header --}}
        <div class="shrink-0 flex items-start justify-between gap-3 px-5 py-4 border-b border-gray-100">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-bold text-gray-900" x-text="notes ? 'Edit Note' : 'Add Note'"></h2>
                    <button
                        type="button"
                        @click.stop="$dispatch('open-sheet', { name: 'help-maintenance-note' })"
                        class="p-1 rounded-lg text-gray-400 hover:text-primary hover:bg-gray-100 transition-colors"
                        title="How notes work"
                        aria-label="Open notes help"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01"/>
                            <circle cx="12" cy="20" r="1" fill="currentColor"/>
                        </svg>
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-0.5" x-text="taskName"></p>
            </div>
            <button type="button" @click="close()"
                class="shrink-0 p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6 6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <x-help.sheet page="maintenance-note" title="How task notes work" />

        {{-- Body --}}
        <form
            method="POST"
            :action="formAction"
            class="flex flex-col flex-1 overflow-hidden"
        >
            @csrf
            @method('PATCH')

            <div class="flex-1 overflow-y-auto px-5 py-5 flex flex-col gap-4">
                <div x-data="{ maxLen: 1000 }" class="flex flex-col gap-1.5">
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-semibold text-gray-700" for="sheet-notes">Note</label>
                        <span
                            class="text-xs tabular-nums"
                            :class="{
                                'text-red-500 font-semibold': notes.length >= maxLen,
                                'text-amber-500':             notes.length >= maxLen * 0.9 && notes.length < maxLen,
                                'text-gray-400':              notes.length < maxLen * 0.9,
                            }"
                            x-text="notes.length + ' / ' + maxLen"
                        ></span>
                    </div>
                    <textarea
                        id="sheet-notes"
                        name="notes"
                        x-model="notes"
                        x-ref="notesTextarea"
                        @open-notes-sheet.window="$nextTick(() => $refs.notesTextarea.focus())"
                        :maxlength="maxLen"
                        rows="5"
                        placeholder="Add a note about this task…"
                        class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-molveno-blue-300 focus:border-molveno-blue-400 text-gray-700 placeholder:text-gray-400 resize-none"
                        :class="{ 'border-red-400 focus:ring-red-200 focus:border-red-400': notes.length >= maxLen }"
                    ></textarea>
                    <p x-show="notes.length >= maxLen" class="text-xs text-red-500">Maximum of 1000 characters reached.</p>
                </div>
            </div>

            {{-- Footer --}}
            <div class="shrink-0 flex items-center justify-end gap-2 px-5 py-4 border-t border-gray-100 bg-white">
                <x-ui.button type="button" variant="secondary" @click="close()">Cancel</x-ui.button>
                <x-ui.button type="submit">Save Note</x-ui.button>
            </div>
        </form>
    </div>
</div>
