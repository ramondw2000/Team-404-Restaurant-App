@props(['tasks', 'emptyMessage' => 'No tasks found.'])

<x-ui.card padding="none">
    <x-ui.table>
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50 text-left">
                <x-ui.th>Task</x-ui.th>
                <x-ui.th>Date</x-ui.th>
                <x-ui.th>Notes <span class="text-gray-300 font-normal normal-case tracking-normal">(click to edit)</span></x-ui.th>
                <x-ui.th>Status</x-ui.th>
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $task)
                <x-maintenance.task-row :task="$task" />
            @empty
                <tr>
                    <td colspan="4">
                        <x-ui.empty-state :title="$emptyMessage" />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-ui.table>
</x-ui.card>
