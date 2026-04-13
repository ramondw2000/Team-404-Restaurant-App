<?php

use App\Enums\MaintenanceTaskStatus;
use App\Models\MaintenanceTask;
use App\Models\User;

it('renders pending and completed maintenance tasks', function () {
    $pending = MaintenanceTask::factory()->create(['name' => 'Fix the sink']);
    $completed = MaintenanceTask::factory()->completed()->create(['name' => 'Replace bulb']);

    $response = $this->actingAs(User::factory()->create())->get(route('maintenance'));

    $response->assertOk();
    $response->assertSee('Fix the sink');
    $response->assertSee('Replace bulb');
    $response->assertSee('Pending Tasks');
    $response->assertSee('Completed Tasks');
    $response->assertSee('Mark as Done', false);
});

it('marks a pending task as done via PATCH', function () {
    $task = MaintenanceTask::factory()->create();

    $response = $this->actingAs(User::factory()->create())
        ->patch(route('maintenance.markAsDone', $task));

    $response->assertRedirect(route('maintenance'));
    expect($task->fresh()->status)->toBe(MaintenanceTaskStatus::Completed);
});

it('updates notes on a task via PATCH', function () {
    $task = MaintenanceTask::factory()->create(['notes' => null]);

    $response = $this->actingAs(User::factory()->create())
        ->patch(route('maintenance.updateNotes', $task), ['notes' => 'Checked the fridge']);

    $response->assertRedirect(route('maintenance'));
    expect($task->fresh()->notes)->toBe('Checked the fridge');
});

it('clears notes when submitting empty value', function () {
    $task = MaintenanceTask::factory()->create(['notes' => 'Old note']);

    $response = $this->actingAs(User::factory()->create())
        ->patch(route('maintenance.updateNotes', $task), ['notes' => null]);

    $response->assertRedirect(route('maintenance'));
    expect($task->fresh()->notes)->toBeNull();
});

it('does not allow marking an already completed task again', function () {
    $task = MaintenanceTask::factory()->completed()->create();

    $response = $this->actingAs(User::factory()->create())
        ->patch(route('maintenance.markAsDone', $task));

    $response->assertRedirect(route('maintenance'));
    expect($task->fresh()->status)->toBe(MaintenanceTaskStatus::Completed);
});
