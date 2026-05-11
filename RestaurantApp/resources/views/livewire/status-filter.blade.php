<div class="flex items-center gap-2">
    <span class="text-xs font-medium text-gray-500 mr-1">Status:</span>
    @foreach(\App\Enums\MaintenanceTaskStatus::cases() as $statusCase)
        @continue($statusCase === \App\Enums\MaintenanceTaskStatus::Unassigned)
        <label class="inline-flex items-center gap-1 text-xs cursor-pointer">
            <input type="checkbox"
                wire:click="toggleStatus('{{ $statusCase->value }}')"
                {{ in_array($statusCase->value, $selectedStatuses) ? 'checked' : '' }}
                class="rounded border-gray-300 text-molveno-blue-500 focus:ring-molveno-blue-300 w-3.5 h-3.5"
            />
            <x-ui.badge :variant="$statusCase->badgeVariant()" size="sm">
                {{ $statusCase->label() }}
                <span class="opacity-70">({{ $statusCounts[$statusCase->value] ?? 0 }})</span>
            </x-ui.badge>
        </label>
    @endforeach
</div>
