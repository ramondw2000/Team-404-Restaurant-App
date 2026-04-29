<div class="flex items-center gap-2">
    <x-ui.badge :variant="$status->badgeVariant()">
        {{ $status->label() }}
    </x-ui.badge>

    @if($status === \App\Enums\MaintenanceTaskStatus::Assigned && $isAssignee)
        <button type="button" wire:click="transitionTo('in_progress')" class="inline-flex items-center gap-1 text-xs font-medium text-molveno-blue-600 hover:text-molveno-blue-800 hover:bg-molveno-blue-50 rounded px-2 py-1 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Start Work
        </button>
    @elseif($status === \App\Enums\MaintenanceTaskStatus::InProgress && $isAssignee)
        <button type="button" wire:click="transitionTo('done')" class="inline-flex items-center gap-1 text-xs font-medium text-green-600 hover:text-green-800 hover:bg-green-50 rounded px-2 py-1 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Mark Done
        </button>
    @elseif($status === \App\Enums\MaintenanceTaskStatus::Done)
        @can('Edit Maintenance Task')
            <button type="button" wire:click="transitionTo('assigned')" class="inline-flex items-center gap-1 text-xs font-medium text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded px-2 py-1 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Reopen
            </button>
        @endcan
    @endif
</div>
