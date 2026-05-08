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

class TaskAssignmentDropdown extends Component
{
    public int $taskId;

    public ?int $assignedUserId = null;

    public bool $dropUp = false;

    public string $userSearch = '';

    public function mount(int $taskId, ?int $assignedUserId = null, bool $dropUp = false): void
    {
        $this->taskId = $taskId;
        $this->assignedUserId = $assignedUserId;
        $this->dropUp = $dropUp;
    }

    protected $listeners = [
        'statusUpdated' => 'refresh',
        'assignmentUpdated' => 'refresh',
    ];

    public function refresh(): void
    {
        $task = MaintenanceTask::find($this->taskId);
        if ($task) {
            $this->assignedUserId = $task->assigned_to;
        }
    }

    /**
     * Users who have the 'View Maintenance' permission, filtered by search.
     *
     * @return Collection<int, User>
     */
    #[Computed]
    public function assignableUsers(): Collection
    {
        return User::permission('View Maintenance')
            ->when($this->userSearch !== '', fn ($q) => $q->where('name', 'like', '%'.$this->userSearch.'%'))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function assignUser(int $userId): void
    {
        $task = MaintenanceTask::findOrFail($this->taskId);
        $currentUser = auth()->user();

        if ($userId !== $currentUser->id && ! $currentUser->can('Assign Maintenance Task')) {
            $this->dispatch('toast', message: 'You do not have permission to assign other users.', type: 'danger');

            return;
        }

        $task->update(['assigned_to' => $userId, 'status' => MaintenanceTaskStatus::Assigned]);
        $this->assignedUserId = $userId;
        $this->userSearch = '';

        $assignedName = User::find($userId)?->name ?? 'Unknown';
        $this->dispatch('toast', message: "Task assigned to {$assignedName}.", type: 'success');
        $this->dispatch('assignmentUpdated');
    }

    public function unassignUser(): void
    {
        $task = MaintenanceTask::findOrFail($this->taskId);
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
        $this->assignedUserId = null;
        $this->userSearch = '';

        $this->dispatch('toast', message: 'Task unassigned.', type: 'success');
        $this->dispatch('assignmentUpdated');
    }

    public function render(): View
    {
        return view('livewire.task-assignment-dropdown');
    }
}
