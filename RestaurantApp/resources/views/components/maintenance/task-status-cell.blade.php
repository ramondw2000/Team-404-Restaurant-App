@props(['task'])

@php
    $status = $task->status;
    $isAssignee = $task->assigned_to === auth()->id();
    $canEdit = auth()->user()->can('Edit Maintenance Task');
    $canTransition = $isAssignee || $canEdit;
@endphp

<div class="flex items-center gap-2">
    @if($status === \App\Enums\MaintenanceTaskStatus::Unassigned)
        <x-ui.badge variant="neutral" size="sm">Unassigned</x-ui.badge>
    @elseif($status === \App\Enums\MaintenanceTaskStatus::Done)
        {{-- Done: Show badge + Reopen button (no dropdown) --}}
        <x-ui.badge :variant="$status->badgeVariant()" size="sm">
            {{ $status->label() }}
        </x-ui.badge>
        @can('Edit Maintenance Task')
            <button type="button" wire:click="transitionStatus({{ $task->id }}, 'unassigned')" class="inline-flex items-center gap-1 text-xs font-medium text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded px-2 py-1 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Reopen
            </button>
        @endcan
    @else
        {{-- Assigned/InProgress: Show dropdown --}}
        <div x-data="{ open: false }" @click.outside="open = false" class="relative">
            <button type="button" @click="open = !open" class="inline-flex items-center gap-1.5 text-sm rounded-lg px-2.5 py-1.5 hover:bg-gray-100 transition-colors">
                <x-ui.badge :variant="$status->badgeVariant()" size="sm">
                    {{ $status->label() }}
                </x-ui.badge>
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" x-transition x-cloak class="absolute z-30 mt-1 left-0 w-40 bg-white rounded-lg shadow-lg border border-gray-200 py-1">
                @foreach([\App\Enums\MaintenanceTaskStatus::Assigned, \App\Enums\MaintenanceTaskStatus::InProgress, \App\Enums\MaintenanceTaskStatus::Done] as $statusOption)
                    @php
                        $isCurrent = $status->value === $statusOption->value;
                        $isDisabled = $isCurrent || !$canTransition;
                    @endphp
                    <button
                        type="button"
                        wire:click="transitionStatus({{ $task->id }}, '{{ $statusOption->value }}')"
                        @click="open = false"
                        @class([
                            'w-full text-left px-3 py-2 text-sm flex items-center gap-2 transition-colors',
                            'bg-gray-50 text-gray-400 cursor-not-allowed' => $isDisabled,
                            'hover:bg-gray-50 text-gray-700' => !$isDisabled,
                        ])
                        @disabled($isDisabled)
                    >
                        <span class="w-2 h-2 rounded-full {{ $statusOption->badgeVariant() === 'warning' ? 'bg-amber-500' : ($statusOption->badgeVariant() === 'primary' ? 'bg-blue-500' : 'bg-green-500') }}"></span>
                        {{ $statusOption->label() }}
                        @if($isCurrent)
                            <svg class="w-4 h-4 text-molveno-blue-500 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    @endif
</div>
