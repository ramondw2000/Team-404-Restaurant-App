<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\MaintenanceTaskStatus;
use App\Models\MaintenanceTask;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class TaskTable extends Component
{
    use WithPagination;

    public ?string $filter = null;

    public ?string $search = null;

    public array $selectedStatuses = [];

    protected $queryString = ['filter', 'search', 'selectedStatuses'];

    protected $listeners = [
        'statusUpdated' => '$refresh',
        'assignmentUpdated' => '$refresh',
        'filterUpdated' => 'updateFilter',
    ];

    public function updateFilter(array $selectedStatuses): void
    {
        $this->selectedStatuses = $selectedStatuses;
    }

    public function mount(?string $filter = null, ?string $search = null, array $selectedStatuses = []): void
    {
        $this->filter = $filter;
        $this->search = $search;
        $this->selectedStatuses = $selectedStatuses;
    }

    public function render(): View
    {
        $tasks = MaintenanceTask::with('assignedUser')
            ->when($this->filter === 'my-tasks', fn ($q) => $q->where('assigned_to', auth()->id()))
            ->when($this->filter === 'unassigned', fn ($q) => $q->where('status', MaintenanceTaskStatus::Unassigned))
            ->when($this->selectedStatuses, fn ($q) => $q->whereIn('status', $this->selectedStatuses))
            ->when($this->search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('notes', 'like', "%{$s}%")
                    ->orWhereHas('assignedUser', fn ($q) => $q->where('name', 'like', "%{$s}%"));
            }))
            ->orderByRaw("
                CASE
                    WHEN status = 'in_progress' THEN 0
                    WHEN status = 'assigned' THEN 1
                    WHEN status = 'unassigned' THEN 2
                    WHEN status = 'done' THEN 3
                END,
                CASE
                    WHEN status = 'done' THEN UNIX_TIMESTAMP(updated_at)
                    ELSE UNIX_TIMESTAMP(created_at)
                END DESC
            ")
            ->paginate(25);

        $statusCounts = [
            'all' => MaintenanceTask::count(),
            'unassigned' => MaintenanceTask::where('status', MaintenanceTaskStatus::Unassigned)->count(),
            'assigned' => MaintenanceTask::where('status', MaintenanceTaskStatus::Assigned)->count(),
            'in_progress' => MaintenanceTask::where('status', MaintenanceTaskStatus::InProgress)->count(),
            'done' => MaintenanceTask::where('status', MaintenanceTaskStatus::Done)->count(),
        ];

        return view('livewire.task-table', [
            'tasks' => $tasks,
            'statusCounts' => $statusCounts,
        ]);
    }
}
