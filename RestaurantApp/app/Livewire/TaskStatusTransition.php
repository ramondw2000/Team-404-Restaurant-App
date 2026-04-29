<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\MaintenanceTaskStatus;
use App\Models\MaintenanceTask;
use Illuminate\View\View;
use Livewire\Component;

class TaskStatusTransition extends Component
{
    public int $taskId;

    public string $currentStatus;

    public bool $isAssignee;

    public function mount(int $taskId, string $currentStatus, bool $isAssignee): void
    {
        $this->taskId = $taskId;
        $this->currentStatus = $currentStatus;
        $this->isAssignee = $isAssignee;
    }

    public function transitionTo(string $status): void
    {
        $task = MaintenanceTask::findOrFail($this->taskId);
        $newStatus = MaintenanceTaskStatus::from($status);
        $currentUser = auth()->user();

        $allowed = match ($newStatus) {
            MaintenanceTaskStatus::InProgress => $task->status === MaintenanceTaskStatus::Assigned && $task->isAssignee($currentUser),
            MaintenanceTaskStatus::Done => $task->status === MaintenanceTaskStatus::InProgress && $task->isAssignee($currentUser),
            MaintenanceTaskStatus::Assigned => $task->status === MaintenanceTaskStatus::Done && $currentUser->can('Edit Maintenance Task'),
        };

        if (! $allowed) {
            $this->dispatch('toast', message: 'You cannot perform this status transition.', type: 'danger');

            return;
        }

        $task->update(['status' => $newStatus]);
        $this->currentStatus = $newStatus->value;
        $this->isAssignee = $task->isAssignee($currentUser);

        $this->dispatch('toast', message: 'Status updated to '.$newStatus->label().'.', type: 'success');
        $this->dispatch('statusUpdated');
    }

    public function render(): View
    {
        $status = MaintenanceTaskStatus::from($this->currentStatus);

        return view('livewire.task-status-transition', [
            'status' => $status,
        ]);
    }
}
