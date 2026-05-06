<?php

namespace App\Http\Controllers;

use App\Enums\MaintenanceTaskStatus;
use App\Models\MaintenanceTask;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    /**
     * Display the maintenance tasks overview with filtering & pagination.
     */
    public function index(Request $request): View
    {
        $tasks = MaintenanceTask::with('assignedUser')
            ->when($request->filter === 'my-tasks', fn ($q) => $q->where('assigned_to', auth()->id()))
            ->when($request->filter === 'unassigned', fn ($q) => $q->whereNull('assigned_to'))
            ->when($request->status, fn ($q, $s) => $q->whereIn('status', (array) $s))
            ->when($request->search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('notes', 'like', "%{$s}%")
                    ->orWhereHas('assignedUser', fn ($q) => $q->where('name', 'like', "%{$s}%"));
            }))
            ->orderByRaw("CASE WHEN status = 'in_progress' THEN 0 WHEN status = 'assigned' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        $statusCounts = [
            'all' => MaintenanceTask::count(),
            'assigned' => MaintenanceTask::where('status', MaintenanceTaskStatus::Assigned)->count(),
            'in_progress' => MaintenanceTask::where('status', MaintenanceTaskStatus::InProgress)->count(),
            'done' => MaintenanceTask::where('status', MaintenanceTaskStatus::Done)->count(),
        ];

        return view('maintenance', [
            'tasks' => $tasks,
            'statusCounts' => $statusCounts,
        ]);
    }

    /**
     * Store a newly created maintenance task.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'notes'    => ['nullable', 'string', 'max:1000'],
        ]);

        MaintenanceTask::create([
            'name'     => $validated['name'],
            'location' => $validated['location'],
            'notes'    => $validated['notes'] ?? null,
        ]);

        return redirect()->route('maintenance')->with('success', 'Task created.');
    }

    /**
     * Update the notes for a maintenance task.
     */
    public function updateNotes(Request $request, MaintenanceTask $task): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $task->update(['notes' => $validated['notes']]);

        return redirect()->route('maintenance')->with('success', 'Notes updated.');
    }

    /**
     * Mark a maintenance task as completed.
     */
    public function markAsDone(MaintenanceTask $task): RedirectResponse
    {
        $this->authorize('Complete Maintenance Task');

        $task->markAsDone();

        return redirect()->route('maintenance')->with('success', 'Task marked as done.');
    }

    /**
     * Assign a user to a maintenance task.
     */
    public function assign(Request $request, MaintenanceTask $task): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $userId = (int) $validated['user_id'];
        $currentUser = $request->user();

        if ($userId !== $currentUser->id && ! $currentUser->can('Assign Maintenance Task')) {
            abort(403, 'You do not have permission to assign other users.');
        }

        $task->update(['assigned_to' => $userId]);

        return redirect()->route('maintenance')->with('success', 'Task assigned.');
    }

    /**
     * Unassign the current assignee from a task.
     */
    public function unassign(Request $request, MaintenanceTask $task): RedirectResponse
    {
        $currentUser = $request->user();

        if (! $task->isAssignee($currentUser) && ! $currentUser->can('Assign Maintenance Task')) {
            abort(403, 'You do not have permission to unassign this task.');
        }

        $task->update(['assigned_to' => null]);

        return redirect()->route('maintenance')->with('success', 'Task unassigned.');
    }

    /**
     * Delete a maintenance task.
     */
    public function destroy(MaintenanceTask $task): RedirectResponse
    {
        $this->authorize('Delete Maintenance Task');

        $task->delete();

        return redirect()->route('maintenance')->with('success', 'Task deleted.');
    }

    /**
     * Transition a task to a new status.
     */
    public function transitionStatus(Request $request, MaintenanceTask $task): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:assigned,in_progress,done'],
        ]);

        $newStatus = MaintenanceTaskStatus::from($validated['status']);
        $currentUser = $request->user();

        $allowed = match ($newStatus) {
            MaintenanceTaskStatus::InProgress => $task->status === MaintenanceTaskStatus::Assigned && $task->isAssignee($currentUser),
            MaintenanceTaskStatus::Done => $task->status === MaintenanceTaskStatus::InProgress && $task->isAssignee($currentUser),
            MaintenanceTaskStatus::Assigned => $task->status === MaintenanceTaskStatus::Done && $currentUser->can('Edit Maintenance Task'),
        };

        if (! $allowed) {
            abort(403, 'You cannot perform this status transition.');
        }

        $task->update(['status' => $newStatus]);

        return redirect()->route('maintenance')->with('success', 'Status updated to '.$newStatus->label().'.');
    }
}
