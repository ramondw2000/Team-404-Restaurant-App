<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\MaintenanceTaskStatus;
use App\Models\MaintenanceTask;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
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

    /**
     * Users who have the 'View Maintenance' permission.
     *
     * @return Collection<int, User>
     */
    #[Computed]
    public function assignableUsers(): Collection
    {
        return User::permission('View Maintenance')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function assignUser(int $taskId, int $userId): void
    {
        $task = MaintenanceTask::findOrFail($taskId);
        $currentUser = auth()->user();

        if ($userId !== $currentUser->id && ! $currentUser->can('Assign Maintenance Task')) {
            $this->dispatch('toast', message: 'You do not have permission to assign other users.', type: 'danger');

            return;
        }

        $task->update(['assigned_to' => $userId, 'status' => MaintenanceTaskStatus::Assigned]);

        $assignedName = User::find($userId)?->name ?? 'Unknown';
        $this->dispatch('toast', message: "Task assigned to {$assignedName}.", type: 'success');
    }

    public function unassignUser(int $taskId): void
    {
        $task = MaintenanceTask::findOrFail($taskId);
        $currentUser = auth()->user();

        if (! $task->isAssignee($currentUser) && ! $currentUser->can('Assign Maintenance Task')) {
            $this->dispatch('toast', message: 'You do not have permission to unassign this task.', type: 'danger');

            return;
        }

        // Cannot unassign if task is done
        if ($task->status === MaintenanceTaskStatus::Done) {
            $this->dispatch('toast', message: 'Cannot unassign a completed task. Reopen it first.', type: 'danger');

            return;
        }

        $task->update(['assigned_to' => null, 'status' => MaintenanceTaskStatus::Unassigned]);

        $this->dispatch('toast', message: 'Task unassigned.', type: 'success');
    }

    public function transitionStatus(int $taskId, string $status): void
    {
        $task = MaintenanceTask::findOrFail($taskId);
        $newStatus = MaintenanceTaskStatus::from($status);
        $currentUser = auth()->user();

        // Assignee can freely change between Assigned, InProgress, and Done
        $assigneeStatuses = [MaintenanceTaskStatus::Assigned, MaintenanceTaskStatus::InProgress, MaintenanceTaskStatus::Done];
        $isAssigneeTransition = in_array($newStatus, $assigneeStatuses) &&
                               in_array($task->status, $assigneeStatuses) &&
                               $task->isAssignee($currentUser);

        // Editors can also change status between Assigned, InProgress, and Done
        $isEditorTransition = in_array($newStatus, $assigneeStatuses) &&
                             in_array($task->status, $assigneeStatuses) &&
                             $currentUser->can('Edit Maintenance Task');

        // Editors can reopen Done tasks to Unassigned
        $isReopenTransition = $newStatus === MaintenanceTaskStatus::Unassigned &&
                             $task->status === MaintenanceTaskStatus::Done &&
                             $currentUser->can('Edit Maintenance Task');

        $allowed = $isAssigneeTransition || $isEditorTransition || $isReopenTransition;

        if (! $allowed) {
            $this->dispatch('toast', message: 'You cannot perform this status transition.', type: 'danger');

            return;
        }

        // If reopening (transitioning to Unassigned), also unassign the assignee
        if ($newStatus === MaintenanceTaskStatus::Unassigned) {
            $task->update(['status' => $newStatus, 'assigned_to' => null]);
        } else {
            $task->update(['status' => $newStatus]);
        }

        $this->dispatch('toast', message: 'Status updated to '.$newStatus->label().'.', type: 'success');
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
                    WHEN status = 'done' THEN updated_at
                    ELSE created_at
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
            'assignableUsers' => $this->assignableUsers,
        ]);
    }
}
