<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\MaintenanceTaskStatus;
use App\Models\MaintenanceTask;
use Illuminate\View\View;
use Livewire\Component;

class StatusFilter extends Component
{
    public array $selectedStatuses = [];

    public ?string $filter = null;

    protected $listeners = ['statusUpdated' => '$refresh', 'assignmentUpdated' => '$refresh'];

    public function mount(?string $filter = null, array $selectedStatuses = []): void
    {
        $this->filter = $filter;
        $this->selectedStatuses = $selectedStatuses;
    }

    public function toggleStatus(string $status): void
    {
        if (in_array($status, $this->selectedStatuses)) {
            $this->selectedStatuses = array_diff($this->selectedStatuses, [$status]);
        } else {
            $this->selectedStatuses[] = $status;
        }

        $this->dispatch('filterUpdated', selectedStatuses: $this->selectedStatuses);
    }

    public function render(): View
    {
        $statusCounts = [
            'all' => MaintenanceTask::count(),
            'unassigned' => MaintenanceTask::where('status', MaintenanceTaskStatus::Unassigned)->count(),
            'assigned' => MaintenanceTask::where('status', MaintenanceTaskStatus::Assigned)->count(),
            'in_progress' => MaintenanceTask::where('status', MaintenanceTaskStatus::InProgress)->count(),
            'done' => MaintenanceTask::where('status', MaintenanceTaskStatus::Done)->count(),
        ];

        return view('livewire.status-filter', [
            'statusCounts' => $statusCounts,
        ]);
    }
}
