<div>
    <x-ui.card padding="none">
        <x-ui.table>
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50 text-left">
                    <x-ui.th>Task</x-ui.th>
                    <x-ui.th>Location</x-ui.th>
                    <x-ui.th>Assigned To</x-ui.th>
                    <x-ui.th>Status</x-ui.th>
                    <x-ui.th>Date Created</x-ui.th>
                    <x-ui.th>Actions</x-ui.th>
                </tr>
            </thead>
            @forelse($tasks as $index => $task)
                <x-maintenance.task-row :task="$task" :index="$index" :total="$tasks->count()" :assignable-users="$assignableUsers" />
            @empty
                <tbody>
                    <tr>
                        <td colspan="6">
                            <x-ui.empty-state title="No tasks found matching your filters." />
                        </td>
                    </tr>
                </tbody>
            @endforelse
        </x-ui.table>
    </x-ui.card>

    {{-- Pagination --}}
    @if($tasks->hasPages())
        <div class="flex justify-center mt-4">
            {{ $tasks->links() }}
        </div>
    @endif
</div>
