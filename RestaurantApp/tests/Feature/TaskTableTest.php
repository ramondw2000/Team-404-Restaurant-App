<?php

use App\Enums\MaintenanceTaskStatus;
use App\Livewire\TaskTable;
use App\Models\MaintenanceTask;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    (new RoleSeeder)->run();
});

function taskTableUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('maintenance_crew');

    return $user;
}

// ── assignUser ──────────────────────────────────────────────────

it('assigns a user to a task via Livewire', function () {
    $user = taskTableUser();
    $task = MaintenanceTask::factory()->create(['status' => MaintenanceTaskStatus::Unassigned]);

    Livewire::actingAs($user)
        ->test(TaskTable::class)
        ->call('assignUser', $task->id, $user->id)
        ->assertDispatched('toast');

    expect($task->fresh())
        ->assigned_to->toBe($user->id)
        ->status->toBe(MaintenanceTaskStatus::Assigned);
});

it('denies assigning others without Assign Maintenance Task permission via Livewire', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('View Maintenance');
    $other = User::factory()->create();
    $task = MaintenanceTask::factory()->create();

    Livewire::actingAs($user)
        ->test(TaskTable::class)
        ->call('assignUser', $task->id, $other->id)
        ->assertDispatched('toast', message: 'You do not have permission to assign other users.', type: 'danger');

    expect($task->fresh()->assigned_to)->not->toBe($other->id);
});

// ── unassignUser ────────────────────────────────────────────────

it('unassigns a user from a task via Livewire', function () {
    $user = taskTableUser();
    $task = MaintenanceTask::factory()->create([
        'assigned_to' => $user->id,
        'status' => MaintenanceTaskStatus::Assigned,
    ]);

    Livewire::actingAs($user)
        ->test(TaskTable::class)
        ->call('unassignUser', $task->id)
        ->assertDispatched('toast', message: 'Task unassigned.', type: 'success');

    expect($task->fresh())
        ->assigned_to->toBeNull()
        ->status->toBe(MaintenanceTaskStatus::Unassigned);
});

it('denies unassigning a done task via Livewire', function () {
    $user = taskTableUser();
    $task = MaintenanceTask::factory()->done()->create(['assigned_to' => $user->id]);

    Livewire::actingAs($user)
        ->test(TaskTable::class)
        ->call('unassignUser', $task->id)
        ->assertDispatched('toast', message: 'Cannot unassign a completed task. Reopen it first.', type: 'danger');

    expect($task->fresh()->assigned_to)->toBe($user->id);
});

it('denies unassigning others without Assign Maintenance Task permission via Livewire', function () {
    $assignee = taskTableUser();
    $other = User::factory()->create();
    $other->givePermissionTo('View Maintenance');
    $task = MaintenanceTask::factory()->create([
        'assigned_to' => $assignee->id,
        'status' => MaintenanceTaskStatus::Assigned,
    ]);

    Livewire::actingAs($other)
        ->test(TaskTable::class)
        ->call('unassignUser', $task->id)
        ->assertDispatched('toast', message: 'You do not have permission to unassign this task.', type: 'danger');

    expect($task->fresh()->assigned_to)->toBe($assignee->id);
});

// ── transitionStatus ────────────────────────────────────────────

it('transitions from Assigned to InProgress for assignee via Livewire', function () {
    $user = taskTableUser();
    $task = MaintenanceTask::factory()->create([
        'assigned_to' => $user->id,
        'status' => MaintenanceTaskStatus::Assigned,
    ]);

    Livewire::actingAs($user)
        ->test(TaskTable::class)
        ->call('transitionStatus', $task->id, 'in_progress')
        ->assertDispatched('toast', message: 'Status updated to In Progress.', type: 'success');

    expect($task->fresh()->status)->toBe(MaintenanceTaskStatus::InProgress);
});

it('transitions from InProgress to Done for assignee via Livewire', function () {
    $user = taskTableUser();
    $task = MaintenanceTask::factory()->create([
        'assigned_to' => $user->id,
        'status' => MaintenanceTaskStatus::InProgress,
    ]);

    Livewire::actingAs($user)
        ->test(TaskTable::class)
        ->call('transitionStatus', $task->id, 'done')
        ->assertDispatched('toast', message: 'Status updated to Done.', type: 'success');

    expect($task->fresh()->status)->toBe(MaintenanceTaskStatus::Done);
});

it('reopens a Done task to Unassigned with Edit permission via Livewire', function () {
    $user = taskTableUser();
    $task = MaintenanceTask::factory()->done()->create(['assigned_to' => $user->id]);

    Livewire::actingAs($user)
        ->test(TaskTable::class)
        ->call('transitionStatus', $task->id, 'unassigned')
        ->assertDispatched('toast', message: 'Status updated to Unassigned.', type: 'success');

    expect($task->fresh())
        ->status->toBe(MaintenanceTaskStatus::Unassigned)
        ->assigned_to->toBeNull();
});

it('denies status transition for non-assignee without Edit permission via Livewire', function () {
    $assignee = taskTableUser();
    $other = User::factory()->create();
    $other->givePermissionTo('View Maintenance');
    $task = MaintenanceTask::factory()->create([
        'assigned_to' => $assignee->id,
        'status' => MaintenanceTaskStatus::Assigned,
    ]);

    Livewire::actingAs($other)
        ->test(TaskTable::class)
        ->call('transitionStatus', $task->id, 'in_progress')
        ->assertDispatched('toast', message: 'You cannot perform this status transition.', type: 'danger');

    expect($task->fresh()->status)->toBe(MaintenanceTaskStatus::Assigned);
});
