<?php

namespace App\Http\Controllers;

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
        $pendingTasks   = MaintenanceTask::pending()->get();
        $completedTasks = MaintenanceTask::completed()->get();

        return view('maintenance', [
            'pendingTasks' => $pendingTasks,
            'completedTasks' => $completedTasks,
        ]);
    }

    /**
     * Store a newly created maintenance task.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        MaintenanceTask::create([
            'name'  => $validated['name'],
            'notes' => $validated['notes'] ?? null,
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
        $task->markAsDone();

        return redirect()->route('maintenance')->with('success', 'Task marked as done.');
    }
}
