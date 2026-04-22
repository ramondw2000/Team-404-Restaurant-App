<?php

use App\Livewire\Reservations;
use App\Models\FloorPlan;
use App\Models\FloorPlanElement;
use App\Models\Reservation;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    (new RoleSeeder)->run();
});

function reservationsUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('management');

    return $user;
}

// ── Rendering ────────────────────────────────────────────────

it('renders the reservations component', function () {
    Livewire::actingAs(reservationsUser())
        ->test(Reservations::class)
        ->assertOk();
});

it('defaults to today as selected date', function () {
    Livewire::actingAs(reservationsUser())
        ->test(Reservations::class)
        ->assertSet('selectedDate', now()->toDateString());
});

// ── Date Navigation ───────────────────────────────────────────

it('changes selected date', function () {
    $date = now()->addDay()->toDateString();

    Livewire::actingAs(reservationsUser())
        ->test(Reservations::class)
        ->call('setDate', $date)
        ->assertSet('selectedDate', $date);
});

it('resets search and status filter when date changes', function () {
    Livewire::actingAs(reservationsUser())
        ->test(Reservations::class)
        ->set('search', 'John')
        ->set('statusFilter', 'late')
        ->call('setDate', now()->addDay()->toDateString())
        ->assertSet('search', '')
        ->assertSet('statusFilter', 'all');
});

// ── Search Filtering ─────────────────────────────────────────

it('filters list by guest name search', function () {
    $date = now()->toDateString();

    Reservation::factory()->forDate($date, '19:00')->create(['guest_name' => 'Alice Smith']);
    Reservation::factory()->forDate($date, '19:00')->create(['guest_name' => 'Bob Jones']);

    Livewire::actingAs(reservationsUser())
        ->test(Reservations::class)
        ->set('search', 'Alice')
        ->assertSee('Alice Smith')
        ->assertDontSee('Bob Jones');
});

// ── Status Filter ─────────────────────────────────────────────

it('filters list by status', function () {
    $date = now()->toDateString();

    $scheduled = Reservation::factory()->forDate($date, '19:30')->create(['guest_name' => 'Sched Person', 'status' => 'scheduled']);
    $arrived = Reservation::factory()->forDate($date, '19:30')->create(['guest_name' => 'Arrived Person', 'status' => 'arrived']);

    Livewire::actingAs(reservationsUser())
        ->test(Reservations::class)
        ->call('setStatusFilter', 'arrived')
        ->assertSee('Arrived Person')
        ->assertDontSee('Sched Person');
});

// ── Status Transitions ────────────────────────────────────────

it('advances status from scheduled to arrived', function () {
    $reservation = Reservation::factory()->forDate(now()->toDateString(), '19:00')->scheduled()->create();

    Livewire::actingAs(reservationsUser())
        ->test(Reservations::class)
        ->call('advanceStatus', $reservation->id);

    expect($reservation->fresh()->status)->toBe('arrived');
});

it('advances status from arrived to departed', function () {
    $reservation = Reservation::factory()->forDate(now()->toDateString(), '19:00')->arrived()->create();

    Livewire::actingAs(reservationsUser())
        ->test(Reservations::class)
        ->call('advanceStatus', $reservation->id);

    expect($reservation->fresh()->status)->toBe('departed');
});

it('does not advance status for departed reservation', function () {
    $reservation = Reservation::factory()->forDate(now()->toDateString(), '19:00')
        ->create(['status' => 'departed']);

    Livewire::actingAs(reservationsUser())
        ->test(Reservations::class)
        ->call('advanceStatus', $reservation->id);

    expect($reservation->fresh()->status)->toBe('departed');
});

it('sets destructive status via setStatus', function () {
    $reservation = Reservation::factory()->forDate(now()->toDateString(), '19:00')->scheduled()->create();

    Livewire::actingAs(reservationsUser())
        ->test(Reservations::class)
        ->call('setStatus', $reservation->id, 'no_show');

    expect($reservation->fresh()->status)->toBe('no_show');
});

it('ignores disallowed status values in setStatus', function () {
    $reservation = Reservation::factory()->forDate(now()->toDateString(), '19:00')->scheduled()->create();

    Livewire::actingAs(reservationsUser())
        ->test(Reservations::class)
        ->call('setStatus', $reservation->id, 'arrived');

    expect($reservation->fresh()->status)->toBe('scheduled');
});

// ── Create ────────────────────────────────────────────────────

it('creates a reservation via the Livewire component', function () {
    $datetime = now()->addDay()->setHour(19)->setMinute(0)->format('Y-m-d\TH:i');

    Livewire::actingAs(reservationsUser())
        ->test(Reservations::class)
        ->set('createGuestName', 'Jane Doe')
        ->set('createPartySize', 3)
        ->set('createDatetime', $datetime)
        ->call('createReservation');

    expect(Reservation::where('guest_name', 'Jane Doe')->exists())->toBeTrue();
});

it('auto-assigns the smallest fitting table when no preference given', function () {
    $plan = FloorPlan::factory()->create();
    $small = FloorPlanElement::factory()->create([
        'floor_plan_id' => $plan->id,
        'seat_count' => 2,
        'table_name' => 'T-small',
    ]);
    $large = FloorPlanElement::factory()->create([
        'floor_plan_id' => $plan->id,
        'seat_count' => 6,
        'table_name' => 'T-large',
    ]);

    $datetime = now()->addDay()->setHour(19)->setMinute(0)->format('Y-m-d\TH:i');

    Livewire::actingAs(reservationsUser())
        ->test(Reservations::class)
        ->set('createGuestName', 'Party Of Two')
        ->set('createPartySize', 2)
        ->set('createDatetime', $datetime)
        ->call('createReservation');

    $reservation = Reservation::where('guest_name', 'Party Of Two')->first();

    expect($reservation)->not->toBeNull()
        ->and($reservation->floor_plan_element_id)->toBe($small->id)
        ->and($reservation->table_number)->toBe($small->table_name);
});

it('falls back to no table when no table fits the party', function () {
    $plan = FloorPlan::factory()->create();
    FloorPlanElement::factory()->create([
        'floor_plan_id' => $plan->id,
        'seat_count' => 2,
        'table_name' => 'T-only',
    ]);

    $datetime = now()->addDay()->setHour(19)->setMinute(0)->format('Y-m-d\TH:i');

    Livewire::actingAs(reservationsUser())
        ->test(Reservations::class)
        ->set('createGuestName', 'Huge Party')
        ->set('createPartySize', 8)
        ->set('createDatetime', $datetime)
        ->call('createReservation');

    $reservation = Reservation::where('guest_name', 'Huge Party')->first();

    expect($reservation)->not->toBeNull()
        ->and($reservation->floor_plan_element_id)->toBeNull();
});

it('fails create validation when datetime is in the past', function () {
    $past = now()->subHour()->format('Y-m-d\TH:i');

    Livewire::actingAs(reservationsUser())
        ->test(Reservations::class)
        ->set('createGuestName', 'Ghost')
        ->set('createPartySize', 2)
        ->set('createDatetime', $past)
        ->call('createReservation')
        ->assertHasErrors(['createDatetime']);
});

// ── Edit ─────────────────────────────────────────────────────

it('updates a reservation via the Livewire component', function () {
    $reservation = Reservation::factory()->forDate(now()->toDateString(), '19:00')->create();

    Livewire::actingAs(reservationsUser())
        ->test(Reservations::class)
        ->call('openEditSheet', $reservation->id)
        ->set('editGuestName', 'Updated Name')
        ->call('updateReservation');

    expect($reservation->fresh()->guest_name)->toBe('Updated Name');
});
