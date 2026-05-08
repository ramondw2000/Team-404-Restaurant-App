<div>
    @if($status === 'unassigned')
        <x-ui.badge variant="neutral" size="sm">Unassigned</x-ui.badge>
    @else
        <livewire:task-status-transition :task-id="$taskId" :current-status="$status" :is-assignee="$isAssignee" :key="'status-'.$taskId.'-'.$status" />
    @endif
</div>
