@props(['task', 'index' => 0, 'total' => 0, 'assignableUsers'])

@php
    $isBottom3 = ($total - $index) <= 3;
@endphp

<tbody x-data="{ expanded: false }">
    <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition-colors">
        <x-ui.td>
            <span class="font-semibold text-gray-900">{{ $task->name }}</span>
        </x-ui.td>
        <x-ui.td class="text-gray-600 text-sm whitespace-nowrap">{{ $task->location }}</x-ui.td>
        <x-ui.td>
            <x-maintenance.task-assignment-cell :task="$task" :assignable-users="$assignableUsers" :drop-up="$isBottom3" />
        </x-ui.td>
        <x-ui.td>
            <x-maintenance.task-status-cell :task="$task" />
        </x-ui.td>
        <x-ui.td class="text-gray-400 text-xs whitespace-nowrap">
            {{ $task->created_at->format('M d, Y') }}
        </x-ui.td>
        <x-ui.td>
            <div class="flex items-center gap-1">
                <button type="button" @click="expanded = !expanded" class="inline-flex items-center gap-1 text-xs font-medium text-molveno-blue-600 hover:text-molveno-blue-800 hover:bg-molveno-blue-50 rounded px-2 py-1 transition-colors">
                    <svg class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                    Requirements
                </button>

                <button type="button"
                    @click="$dispatch('open-notes-sheet', {
                        id: {{ $task->id }},
                        notes: '{{ addslashes($task->notes ?? '') }}',
                        taskName: '{{ addslashes($task->name) }}'
                    })"
                    class="inline-flex items-center gap-1 text-xs font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded px-2 py-1 transition-colors"
                    title="Edit note">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    Notes
                </button>

                @can('Delete Maintenance Task')
                    <button type="button"
                        @click="window.confirmAction({
                            title: 'Delete Task',
                            message: 'Are you sure you want to delete this task? This action cannot be undone.',
                            confirmLabel: 'Delete',
                            variant: 'danger',
                            onConfirm: () => { document.getElementById('delete-task-{{ $task->id }}').submit(); },
                        })"
                        class="inline-flex items-center gap-1 text-xs font-medium text-red-500 hover:text-red-700 hover:bg-red-50 rounded px-2 py-1 transition-colors"
                        title="Delete task">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <polyline points="3 6 5 6 21 6"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m5 0V4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2"/>
                        </svg>
                        Delete
                    </button>
                    <form id="delete-task-{{ $task->id }}" method="POST" action="{{ route('maintenance.destroy', $task) }}" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                @endcan
            </div>
        </x-ui.td>
    </tr>
    <tr x-show="expanded" x-collapse x-cloak>
        <td colspan="6" class="p-0">
            <livewire:maintenance-task-requirements :task-id="$task->id" lazy :key="'req-'.$task->id" />
        </td>
    </tr>
</tbody>
