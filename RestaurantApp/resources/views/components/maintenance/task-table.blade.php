@props(['tasks', 'emptyMessage' => 'No tasks found.'])

@php
    $totalTasks = $tasks->count();
@endphp

<x-ui.card padding="none">
    <x-ui.table>
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50 text-left">
                <x-ui.th>Task</x-ui.th>
                <x-ui.th>Location</x-ui.th>
                <x-ui.th>Assigned To</x-ui.th>
                <x-ui.th>Status</x-ui.th>
                <x-ui.th>Date Created</x-ui.th>
                <x-ui.th>Done At</x-ui.th>
                <x-ui.th>Completion Date</x-ui.th>
            </tr>
        </thead>
        @forelse($tasks as $index => $task)
            <x-maintenance.task-row :task="$task" :index="$index" :total="$totalTasks" />
        @empty
            <tbody>
                <tr>
                    <td colspan="7">
                        <x-ui.empty-state :title="$emptyMessage" />
                    </td>
                </tr>
            </tbody>
        @endforelse
    </x-ui.table>
</x-ui.card>
