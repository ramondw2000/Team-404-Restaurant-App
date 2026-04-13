<?php

namespace App\Http\Controllers;

use App\Enums\MaintenanceTaskStatus;
use App\Models\MaintenanceTask;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    /**
     * Display the maintenance tasks overview.
     */
    public function index(): View
    {
        $pendingTasks = MaintenanceTask::where('status', MaintenanceTaskStatus::Pending)
            ->orderBy('created_at', 'desc')
            ->get();

        $completedTasks = MaintenanceTask::where('status', MaintenanceTaskStatus::Completed)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('maintenance.index', [
            'pendingTasks' => $pendingTasks,
            'completedTasks' => $completedTasks,
        ]);
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
        $task->update(['status' => MaintenanceTaskStatus::Completed]);

        return redirect()->route('maintenance')->with('success', 'Task marked as done.');
    }
}
