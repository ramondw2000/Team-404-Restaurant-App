<?php

use App\Enums\MaintenanceTaskStatus;
use App\Models\MaintenanceTask;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    (new RoleSeeder)->run();
});

/**
 * Helper: create a user with the maintenance_crew role.
 */
function maintenanceUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('maintenance_crew');

    return $user;
}

// ── Page rendering ────────────────────────────────────────────

it('renders maintenance tasks with the new column layout', function () {
    $task = MaintenanceTask::factory()->create(['name' => 'Fix the sink']);
    $done = MaintenanceTask::factory()->done()->create(['name' => 'Replace bulb']);

    $response = $this->actingAs(maintenanceUser())->get(route('maintenance'));

    $response->assertOk();
    $response->assertSee('Fix the sink');
    $response->assertSee('Replace bulb');
    $response->assertSee('Assigned To');
    $response->assertSee('Status');
});

it('denies access to the maintenance page without View Maintenance permission', function () {
    $user = User::factory()->create();
    $user->assignRole('server');

    $this->actingAs($user)->get(route('maintenance'))->assertForbidden();
});

it('filters by my-tasks', function () {
    $user = maintenanceUser();
    $myTask = MaintenanceTask::factory()->create(['name' => 'My task', 'assigned_to' => $user->id]);
    $otherTask = MaintenanceTask::factory()->create(['name' => 'Other task', 'assigned_to' => null]);

    $response = $this->actingAs($user)->get(route('maintenance', ['filter' => 'my-tasks']));

    $response->assertOk();
    $response->assertSee('My task');
    $response->assertDontSee('Other task');
});

it('filters by unassigned', function () {
    $user = maintenanceUser();
    MaintenanceTask::factory()->create(['name' => 'Assigned task', 'assigned_to' => $user->id]);
    MaintenanceTask::factory()->create(['name' => 'Unassigned task', 'assigned_to' => null]);

    $response = $this->actingAs($user)->get(route('maintenance', ['filter' => 'unassigned']));

    $response->assertOk();
    $response->assertSee('Unassigned task');
    $response->assertDontSee('Assigned task');
});

it('filters by status', function () {
    $user = maintenanceUser();
    MaintenanceTask::factory()->create(['name' => 'Assigned one', 'status' => MaintenanceTaskStatus::Assigned]);
    MaintenanceTask::factory()->done()->create(['name' => 'Done one']);

    $response = $this->actingAs($user)->get(route('maintenance', ['status' => ['done']]));

    $response->assertOk();
    $response->assertSee('Done one');
    $response->assertDontSee('Assigned one');
});

it('searches by task name', function () {
    $user = maintenanceUser();
    MaintenanceTask::factory()->create(['name' => 'Repair fridge']);
    MaintenanceTask::factory()->create(['name' => 'Clean floor']);

    $response = $this->actingAs($user)->get(route('maintenance', ['search' => 'fridge']));

    $response->assertOk();
    $response->assertSee('Repair fridge');
    $response->assertDontSee('Clean floor');
});

// ── Store: create new task ────────────────────────────────────

it('creates a new maintenance task via POST', function () {
    $response = $this->actingAs(maintenanceUser())
        ->post(route('maintenance.store'), ['name' => 'Replace broken window', 'location' => 'Kitchen']);

    $response->assertRedirect(route('maintenance'));
    expect(MaintenanceTask::where('name', 'Replace broken window')->exists())->toBeTrue();
});

it('creates a new task with an optional note via POST', function () {
    $response = $this->actingAs(maintenanceUser())
        ->post(route('maintenance.store'), ['name' => 'Fix ceiling light', 'location' => 'Bar', 'notes' => 'Use the tall ladder']);

    $response->assertRedirect(route('maintenance'));

    $task = MaintenanceTask::where('name', 'Fix ceiling light')->first();
    expect($task)->not->toBeNull()
        ->and($task->notes)->toBe('Use the tall ladder');
});

it('requires a name when creating a maintenance task', function () {
    $response = $this->actingAs(maintenanceUser())
        ->post(route('maintenance.store'), ['name' => '', 'location' => 'Bar']);

    $response->assertSessionHasErrors('name');
});

it('denies task creation without Create Maintenance Task permission', function () {
    $user = User::factory()->create();
    $user->assignRole('server');

    $this->actingAs($user)
        ->post(route('maintenance.store'), ['name' => 'Fix the sink', 'location' => 'Kitchen'])
        ->assertForbidden();
});

// ── Mark as done ──────────────────────────────────────────────

it('marks a task as done via PATCH', function () {
    $task = MaintenanceTask::factory()->create();

    $response = $this->actingAs(maintenanceUser())
        ->patch(route('maintenance.markAsDone', $task));

    $response->assertRedirect(route('maintenance'));
    expect($task->fresh()->status)->toBe(MaintenanceTaskStatus::Done);
});

it('does not allow marking an already done task again', function () {
    $task = MaintenanceTask::factory()->done()->create();

    $response = $this->actingAs(maintenanceUser())
        ->patch(route('maintenance.markAsDone', $task));

    $response->assertRedirect(route('maintenance'));
    expect($task->fresh()->status)->toBe(MaintenanceTaskStatus::Done);
});

it('denies marking a task as done without Complete Maintenance Task permission', function () {
    $task = MaintenanceTask::factory()->create();
    $user = User::factory()->create();
    $user->assignRole('server');

    $this->actingAs($user)
        ->patch(route('maintenance.markAsDone', $task))
        ->assertForbidden();
});

// ── Notes ─────────────────────────────────────────────────────

it('updates notes on a task via PATCH', function () {
    $task = MaintenanceTask::factory()->create(['notes' => null]);

    $response = $this->actingAs(maintenanceUser())
        ->patch(route('maintenance.updateNotes', $task), ['notes' => 'Checked the fridge']);

    $response->assertRedirect(route('maintenance'));
    expect($task->fresh()->notes)->toBe('Checked the fridge');
});

it('clears notes when submitting empty value', function () {
    $task = MaintenanceTask::factory()->create(['notes' => 'Old note']);

    $response = $this->actingAs(maintenanceUser())
        ->patch(route('maintenance.updateNotes', $task), ['notes' => null]);

    $response->assertRedirect(route('maintenance'));
    expect($task->fresh()->notes)->toBeNull();
});

it('denies updating notes without Edit Maintenance Task permission', function () {
    $task = MaintenanceTask::factory()->create(['notes' => null]);
    $user = User::factory()->create();
    $user->assignRole('server');

    $this->actingAs($user)
        ->patch(route('maintenance.updateNotes', $task), ['notes' => 'Some note'])
        ->assertForbidden();
});

// ── Assignment ────────────────────────────────────────────────

it('allows self-assignment with View Maintenance permission', function () {
    $user = maintenanceUser();
    $task = MaintenanceTask::factory()->create();

    $response = $this->actingAs($user)
        ->patch(route('maintenance.assign', $task), ['user_id' => $user->id]);

    $response->assertRedirect(route('maintenance'));
    expect($task->fresh()->assigned_to)->toBe($user->id);
});

it('allows assigning others with Assign Maintenance Task permission', function () {
    $assigner = maintenanceUser();
    $assignee = maintenanceUser();
    $task = MaintenanceTask::factory()->create();

    $response = $this->actingAs($assigner)
        ->patch(route('maintenance.assign', $task), ['user_id' => $assignee->id]);

    $response->assertRedirect(route('maintenance'));
    expect($task->fresh()->assigned_to)->toBe($assignee->id);
});

it('denies assigning others without Assign Maintenance Task permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('View Maintenance');
    $other = User::factory()->create();
    $task = MaintenanceTask::factory()->create();

    $this->actingAs($user)
        ->patch(route('maintenance.assign', $task), ['user_id' => $other->id])
        ->assertForbidden();
});

it('allows self-unassignment', function () {
    $user = maintenanceUser();
    $task = MaintenanceTask::factory()->create(['assigned_to' => $user->id]);

    $response = $this->actingAs($user)
        ->patch(route('maintenance.unassign', $task));

    $response->assertRedirect(route('maintenance'));
    expect($task->fresh()->assigned_to)->toBeNull();
});

it('denies unassigning others without Assign Maintenance Task permission', function () {
    $assignee = maintenanceUser();
    $other = User::factory()->create();
    $other->givePermissionTo('View Maintenance');
    $task = MaintenanceTask::factory()->create(['assigned_to' => $assignee->id]);

    $this->actingAs($other)
        ->patch(route('maintenance.unassign', $task))
        ->assertForbidden();
});

// ── Status transitions ───────────────────────────────────────

it('transitions from Assigned to InProgress for assignee', function () {
    $user = maintenanceUser();
    $task = MaintenanceTask::factory()->create(['assigned_to' => $user->id, 'status' => MaintenanceTaskStatus::Assigned]);

    $response = $this->actingAs($user)
        ->patch(route('maintenance.transitionStatus', $task), ['status' => 'in_progress']);

    $response->assertRedirect(route('maintenance'));
    expect($task->fresh()->status)->toBe(MaintenanceTaskStatus::InProgress);
});

it('transitions from InProgress to Done for assignee', function () {
    $user = maintenanceUser();
    $task = MaintenanceTask::factory()->create(['assigned_to' => $user->id, 'status' => MaintenanceTaskStatus::InProgress]);

    $response = $this->actingAs($user)
        ->patch(route('maintenance.transitionStatus', $task), ['status' => 'done']);

    $response->assertRedirect(route('maintenance'));
    expect($task->fresh()->status)->toBe(MaintenanceTaskStatus::Done);
});

it('allows reopening a Done task with Edit Maintenance Task permission', function () {
    $user = maintenanceUser();
    $task = MaintenanceTask::factory()->done()->create();

    $response = $this->actingAs($user)
        ->patch(route('maintenance.transitionStatus', $task), ['status' => 'assigned']);

    $response->assertRedirect(route('maintenance'));
    expect($task->fresh()->status)->toBe(MaintenanceTaskStatus::Assigned);
});

it('denies non-assignee from starting work', function () {
    $assignee = maintenanceUser();
    $other = maintenanceUser();
    $task = MaintenanceTask::factory()->create(['assigned_to' => $assignee->id, 'status' => MaintenanceTaskStatus::Assigned]);

    $this->actingAs($other)
        ->patch(route('maintenance.transitionStatus', $task), ['status' => 'in_progress'])
        ->assertForbidden();
});

it('denies invalid status transitions', function () {
    $user = maintenanceUser();
    $task = MaintenanceTask::factory()->create(['assigned_to' => $user->id, 'status' => MaintenanceTaskStatus::Assigned]);

    $this->actingAs($user)
        ->patch(route('maintenance.transitionStatus', $task), ['status' => 'done'])
        ->assertForbidden();
});

// ── Model ─────────────────────────────────────────────────────

it('returns assigned user name or fallback', function () {
    $user = User::factory()->create(['name' => 'John Doe']);
    $task = MaintenanceTask::factory()->create(['assigned_to' => $user->id]);

    expect($task->assignedUserName())->toBe('John Doe');

    $unassigned = MaintenanceTask::factory()->create(['assigned_to' => null]);
    expect($unassigned->assignedUserName())->toBe('Unassigned');
});

it('detects assignee correctly', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $task = MaintenanceTask::factory()->create(['assigned_to' => $user->id]);

    expect($task->isAssignee($user))->toBeTrue();
    expect($task->isAssignee($other))->toBeFalse();
});

// ── Foreign key cascade ──────────────────────────────────────

it('sets assigned_to to null when user is deleted', function () {
    $user = User::factory()->create();
    $task = MaintenanceTask::factory()->create(['assigned_to' => $user->id]);

    $user->delete();

    expect($task->fresh()->assigned_to)->toBeNull();
    expect($task->fresh()->assignedUserName())->toBe('Unassigned');
});
