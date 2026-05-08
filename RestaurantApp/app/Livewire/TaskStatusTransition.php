<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\MaintenanceTaskStatus;
use App\Models\MaintenanceTask;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class TaskStatusTransition extends Component
{
    public int $taskId;

    public string $currentStatus;

    public bool $isAssignee;

    protected $listeners = ['assignmentUpdated' => 'refreshStatus'];

    public function mount(int $taskId, string $currentStatus, bool $isAssignee): void
    {
        $this->taskId = $taskId;
        $this->currentStatus = $currentStatus;
        $this->isAssignee = $isAssignee;
    }

    #[On('assignmentUpdated')]
    public function refreshStatus(): void
    {
        $task = MaintenanceTask::find($this->taskId);
        if ($task) {
            $this->currentStatus = $task->status->value;
            $this->isAssignee = $task->isAssignee(auth()->user());
        }
    }

    public function transitionTo(string $status): void
    {
        $task = MaintenanceTask::findOrFail($this->taskId);
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
            $isAssigneeCheck = $task->isAssignee($currentUser);
            $debug = "isAssignee: " . ($isAssigneeCheck ? 'true' : 'false') . ", currentStatus: " . $task->status->value . ", newStatus: " . $newStatus->value;
            $this->dispatch('toast', message: 'You cannot perform this status transition. (' . $debug . ')', type: 'danger');

            return;
        }

        // If reopening (transitioning to Unassigned), also unassign the assignee
        if ($newStatus === MaintenanceTaskStatus::Unassigned) {
            $task->update(['status' => $newStatus, 'assigned_to' => null]);
        } else {
            $task->update(['status' => $newStatus]);
        }

        $task->refresh();
        $this->currentStatus = $newStatus->value;
        $this->isAssignee = $task->isAssignee($currentUser);

        $this->dispatch('toast', message: 'Status updated to '.$newStatus->label().'.', type: 'success');
        $this->dispatch('statusUpdated');

        // Also dispatch assignmentUpdated if we unassigned
        if ($newStatus === MaintenanceTaskStatus::Unassigned) {
            $this->dispatch('assignmentUpdated');
        }
    }

    public function render(): View
    {
        $status = MaintenanceTaskStatus::from($this->currentStatus);

        return view('livewire.task-status-transition', [
            'status' => $status,
        ]);
    }
}
