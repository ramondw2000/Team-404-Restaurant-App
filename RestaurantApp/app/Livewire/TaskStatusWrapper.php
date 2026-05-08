<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\MaintenanceTask;
use Illuminate\View\View;
use Livewire\Component;

class TaskStatusWrapper extends Component
{
    public int $taskId;

    public string $status;

    public bool $isAssignee;

    protected $listeners = [
        'assignmentUpdated' => 'refresh',
        'statusUpdated' => 'refresh',
    ];

    public function mount(int $taskId, string $status, bool $isAssignee): void
    {
        $this->taskId = $taskId;
        $this->status = $status;
        $this->isAssignee = $isAssignee;
    }

    public function refresh(): void
    {
        $task = MaintenanceTask::find($this->taskId);
        if ($task) {
            $this->status = $task->status->value;
            $this->isAssignee = $task->isAssignee(auth()->user());
        }
    }

    public function render(): View
    {
        return view('livewire.task-status-wrapper');
    }
}
