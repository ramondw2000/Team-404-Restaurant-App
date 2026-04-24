@props(['task'])

<tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors">
    <x-ui.td>
        <span class="font-semibold text-gray-900">{{ $task->name }}</span>
    </x-ui.td>
    <x-ui.td class="text-gray-600 text-sm whitespace-nowrap">{{ $task->location }}</x-ui.td>
    <x-ui.td class="text-gray-400 text-xs whitespace-nowrap">
        {{ $task->created_at->format('M d, Y') }}
    </x-ui.td>
    <x-maintenance.task-notes-cell :task="$task" />
    <x-ui.td>
        <div class="flex items-center gap-2">
            <x-ui.badge variant="{{ $task->status === \App\Enums\MaintenanceTaskStatus::Pending ? 'warning' : 'success' }}">
                {{ $task->status->label() }}
            </x-ui.badge>
            @if($task->status === \App\Enums\MaintenanceTaskStatus::Pending)
                <form method="POST" action="{{ route('maintenance.markAsDone', $task) }}">
                    @csrf
                    @method('PATCH')
                    <x-ui.button type="submit" variant="primary" size="sm">Mark as Done</x-ui.button>
                </form>
            @endif
        </div>
    </x-ui.td>
</tr>
