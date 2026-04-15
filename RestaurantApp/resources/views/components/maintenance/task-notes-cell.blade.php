@props(['task'])

<td class="px-4 py-3 min-w-[200px]" x-data>
    <div class="flex items-start justify-between gap-2">
        @if($task->notes)
            <span class="text-sm text-gray-700 leading-snug">{{ $task->notes }}</span>
        @else
            <span class="text-sm text-gray-400 italic">No note added</span>
        @endif
        <button type="button"
            @click="$dispatch('open-notes-sheet', {
                id: {{ $task->id }},
                notes: '{{ addslashes($task->notes ?? '') }}',
                taskName: '{{ addslashes($task->name) }}'
            })"
            class="shrink-0 inline-flex items-center gap-1 text-xs font-medium text-molveno-blue-600 hover:text-molveno-blue-800 hover:bg-molveno-blue-50 rounded px-1.5 py-0.5 transition-colors"
            title="Edit note">
            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
            </svg>
            {{ $task->notes ? 'Edit note' : 'Add note' }}
        </button>
    </div>
</td>
