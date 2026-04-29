<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\MaintenanceTask;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class MaintenanceTaskRequirements extends Component
{
    public int $taskId;

    /** @var array<int, array{id: string, content: string, is_completed: bool, sort_order: int}> */
    public array $items = [];

    public string $newItemContent = '';

    public bool $canEdit = false;

    public bool $canToggle = false;

    public function mount(int $taskId): void
    {
        $this->taskId = $taskId;
        $this->loadItems();
        $this->computePermissions();
    }

    public function addItem(): void
    {
        if (! $this->canEdit || trim($this->newItemContent) === '') {
            return;
        }

        $this->items[] = [
            'id' => Str::uuid()->toString(),
            'content' => trim($this->newItemContent),
            'is_completed' => false,
            'sort_order' => count($this->items),
        ];

        $this->newItemContent = '';
        $this->saveItems();
    }

    public function updateItem(string $itemId, string $content): void
    {
        if (! $this->canEdit) {
            return;
        }

        foreach ($this->items as &$item) {
            if ($item['id'] === $itemId) {
                $item['content'] = $content;
                break;
            }
        }
        unset($item);

        $this->saveItems();
    }

    public function toggleCompleted(string $itemId): void
    {
        if (! $this->canToggle) {
            return;
        }

        foreach ($this->items as &$item) {
            if ($item['id'] === $itemId) {
                $item['is_completed'] = ! $item['is_completed'];
                break;
            }
        }
        unset($item);

        $this->saveItems();
    }

    public function deleteItem(string $itemId): void
    {
        if (! $this->canEdit) {
            return;
        }

        $this->items = array_values(array_filter(
            $this->items,
            fn (array $item) => $item['id'] !== $itemId,
        ));

        $this->reindex();
        $this->saveItems();
    }

    /**
     * @param array<int, array{order: int, value: string}> $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        if (! $this->canEdit) {
            return;
        }

        $indexed = collect($this->items)->keyBy('id');
        $reordered = [];

        foreach ($orderedIds as $position => $id) {
            if ($indexed->has($id)) {
                $item = $indexed->get($id);
                $item['sort_order'] = $position;
                $reordered[] = $item;
            }
        }

        $this->items = $reordered;
        $this->saveItems();
    }

    public function render(): View
    {
        return view('livewire.maintenance-task-requirements');
    }

    private function loadItems(): void
    {
        $task = MaintenanceTask::findOrFail($this->taskId);
        $this->items = $task->requirements ?? [];
    }

    private function saveItems(): void
    {
        $task = MaintenanceTask::findOrFail($this->taskId);
        $task->update(['requirements' => $this->items]);
    }

    private function reindex(): void
    {
        foreach ($this->items as $index => &$item) {
            $item['sort_order'] = $index;
        }
        unset($item);
    }

    private function computePermissions(): void
    {
        $task = MaintenanceTask::findOrFail($this->taskId);
        $user = auth()->user();

        $isAssignee = $task->isAssignee($user);
        $isAdmin = $user->can('Assign Maintenance Task') || $user->hasRole('management');

        $this->canEdit = $isAssignee || $isAdmin;
        $this->canToggle = $isAssignee;
    }
}
