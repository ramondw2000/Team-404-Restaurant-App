<?php

use App\Enums\MaintenanceTaskStatus;
use App\Models\MaintenanceTask;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    (new RoleSeeder)->run();
});

/**
 * Helper: create a user with the given role.
 */
function maintenanceUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('maintenance_crew');

    return $user;
}

// ── Page rendering ────────────────────────────────────────────

it('renders pending and completed maintenance tasks', function () {
    $pending = MaintenanceTask::factory()->create(['name' => 'Fix the sink']);
    $completed = MaintenanceTask::factory()->completed()->create(['name' => 'Replace bulb']);

    $response = $this->actingAs(maintenanceUser())->get(route('maintenance'));

    $response->assertOk();
    $response->assertSee('Fix the sink');
    $response->assertSee('Replace bulb');
    $response->assertSee('Pending Tasks');
    $response->assertSee('Completed Tasks');
    $response->assertSee('Mark as Done', false);
});

it('denies access to the maintenance page without View Maintenance permission', function () {
    $user = User::factory()->create();
    $user->assignRole('server');

    $this->actingAs($user)->get(route('maintenance'))->assertForbidden();
});

// ── Store: create new task ────────────────────────────────────

it('creates a new maintenance task via POST', function () {
    $response = $this->actingAs(maintenanceUser())
        ->post(route('maintenance.store'), ['name' => 'Replace broken window']);

    $response->assertRedirect(route('maintenance'));
    expect(MaintenanceTask::where('name', 'Replace broken window')->exists())->toBeTrue();
});

it('creates a new task with an optional note via POST', function () {
    $response = $this->actingAs(maintenanceUser())
        ->post(route('maintenance.store'), ['name' => 'Fix ceiling light', 'notes' => 'Use the tall ladder']);

    $response->assertRedirect(route('maintenance'));

    $task = MaintenanceTask::where('name', 'Fix ceiling light')->first();
    expect($task)->not->toBeNull()
        ->and($task->notes)->toBe('Use the tall ladder');
});

it('requires a name when creating a maintenance task', function () {
    $response = $this->actingAs(maintenanceUser())
        ->post(route('maintenance.store'), ['name' => '']);

    $response->assertSessionHasErrors('name');
});

it('denies task creation without Create Maintenance Task permission', function () {
    $user = User::factory()->create();
    $user->assignRole('server');

    $this->actingAs($user)
        ->post(route('maintenance.store'), ['name' => 'Fix the sink'])
        ->assertForbidden();
});

// ── Mark as done ──────────────────────────────────────────────

it('marks a pending task as done via PATCH', function () {
    $task = MaintenanceTask::factory()->create();

    $response = $this->actingAs(maintenanceUser())
        ->patch(route('maintenance.markAsDone', $task));

    $response->assertRedirect(route('maintenance'));
    expect($task->fresh()->status)->toBe(MaintenanceTaskStatus::Completed);
});

it('does not allow marking an already completed task again', function () {
    $task = MaintenanceTask::factory()->completed()->create();

    $response = $this->actingAs(maintenanceUser())
        ->patch(route('maintenance.markAsDone', $task));

    $response->assertRedirect(route('maintenance'));
    expect($task->fresh()->status)->toBe(MaintenanceTaskStatus::Completed);
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
