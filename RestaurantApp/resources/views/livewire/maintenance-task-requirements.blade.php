<div class="px-4 py-3 bg-gray-50/50">
    <div class="flex items-center justify-between mb-3">
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Requirements Checklist</h4>
        @if(count($items) > 0)
            <span class="text-xs text-gray-400">
                {{ collect($items)->where('is_completed', true)->count() }} / {{ count($items) }} completed
            </span>
        @endif
    </div>

    @if(count($items) === 0 && !$canEdit)
        <x-ui.empty-state title="No requirements added yet." />
    @else
        <div class="flex flex-col gap-1.5" wire:sortable="reorder">
            @foreach($items as $index => $item)
                <div wire:sortable.item="{{ $item['id'] }}" wire:key="req-{{ $item['id'] }}" class="flex items-start gap-2 group bg-white rounded-lg border border-gray-100 px-3 py-2">
                    @if($canEdit)
                        <button type="button" wire:sortable.handle class="mt-0.5 cursor-grab text-gray-300 hover:text-gray-500 shrink-0">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M7 2a2 2 0 10.001 4.001A2 2 0 007 2zm0 6a2 2 0 10.001 4.001A2 2 0 007 8zm0 6a2 2 0 10.001 4.001A2 2 0 007 14zm6-8a2 2 0 10-.001-4.001A2 2 0 0013 6zm0 2a2 2 0 10.001 4.001A2 2 0 0013 8zm0 6a2 2 0 10.001 4.001A2 2 0 0013 14z"/>
                            </svg>
                        </button>
                    @endif

                    <input type="checkbox"
                        {{ $item['is_completed'] ? 'checked' : '' }}
                        {{ $canToggle ? '' : 'disabled' }}
                        wire:click="toggleCompleted('{{ $item['id'] }}')"
                        class="mt-1 rounded border-gray-300 text-molveno-blue-500 focus:ring-molveno-blue-300 shrink-0 {{ $canToggle ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' }}"
                    />

                    <div class="flex-1 min-w-0">
                        @if($canEdit)
                            <input type="text"
                                value="{{ $item['content'] }}"
                                wire:blur="updateItem('{{ $item['id'] }}', $event.target.value)"
                                class="w-full text-sm border-0 bg-transparent px-0 py-0 focus:ring-0 {{ $item['is_completed'] ? 'text-gray-400 line-through' : 'text-gray-700' }}"
                            />
                        @else
                            <span class="text-sm {{ $item['is_completed'] ? 'text-gray-400 line-through' : 'text-gray-700' }}">
                                {{ $item['content'] }}
                            </span>
                        @endif
                    </div>

                    @if($canEdit)
                        <button type="button" wire:click="deleteItem('{{ $item['id'] }}')" class="mt-0.5 opacity-0 group-hover:opacity-100 text-gray-300 hover:text-red-500 transition-all shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    @endif
                </div>
            @endforeach
        </div>

        @if($canEdit)
            <div class="mt-2 flex items-center gap-2">
                <input type="text"
                    wire:model="newItemContent"
                    wire:keydown.enter="addItem"
                    placeholder="Add requirement…"
                    class="flex-1 text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-molveno-blue-300 focus:border-molveno-blue-400 placeholder:text-gray-400"
                />
                <button type="button" wire:click="addItem" class="inline-flex items-center gap-1 text-xs font-medium text-molveno-blue-600 hover:text-molveno-blue-800 hover:bg-molveno-blue-50 rounded px-2.5 py-1.5 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add
                </button>
            </div>
        @endif
    @endif
</div>
