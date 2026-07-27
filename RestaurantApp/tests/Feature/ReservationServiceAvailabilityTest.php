<?php

use App\Models\FloorPlan;
use App\Models\FloorPlanElement;
use App\Models\Reservation;
use App\Services\ReservationService;
use Carbon\Carbon;

// ── getAvailableTablesAt ───────────────────────────────────────

it('returns tables with enough seats that have no conflicting reservation', function () {
    $plan = FloorPlan::factory()->create();
    $table = FloorPlanElement::factory()->create([
        'floor_plan_id' => $plan->id,
        'seat_count' => 4,
        'table_name' => 'T1',
    ]);

    $dateTime = Carbon::parse('2030-01-15 19:00');

    $results = app(ReservationService::class)->getAvailableTablesAt($dateTime, 2);

    expect($results->pluck('id'))->toContain($table->id);
});

it('excludes tables with an overlapping reservation', function () {
    $plan = FloorPlan::factory()->create();
    $table = FloorPlanElement::factory()->create([
        'floor_plan_id' => $plan->id,
        'seat_count' => 4,
        'table_name' => 'T2',
    ]);

    $dateTime = Carbon::parse('2030-01-15 19:00');

    Reservation::factory()->create([
        'floor_plan_element_id' => $table->id,
        'reservation_datetime' => $dateTime,
        'status' => 'scheduled',
    ]);

    $results = app(ReservationService::class)->getAvailableTablesAt($dateTime, 2);

    expect($results->pluck('id'))->not->toContain($table->id);
});

// ── getStayEndForElement ──────────────────────────────────────

it('returns null when no guest is seated at the table', function () {
    $plan = FloorPlan::factory()->create();
    $table = FloorPlanElement::factory()->create(['floor_plan_id' => $plan->id]);

    expect(app(ReservationService::class)->getStayEndForElement($table->id))->toBeNull();
});

it('returns seated_at + 2 hours when no later booking exists', function () {
    $plan = FloorPlan::factory()->create();
    $table = FloorPlanElement::factory()->create(['floor_plan_id' => $plan->id]);

    $reservedAt = Carbon::parse('2030-01-15 19:00');
    $seatedAt = Carbon::parse('2030-01-15 19:15');
    Reservation::factory()->arrived()->create([
        'floor_plan_element_id' => $table->id,
        'reservation_datetime' => $reservedAt,
        'seated_at' => $seatedAt,
    ]);

    expect(app(ReservationService::class)->getStayEndForElement($table->id)->toDateTimeString())
        ->toBe($seatedAt->copy()->addHours(2)->toDateTimeString());
});

it('falls back to reservation_datetime + 2 hours when seated_at is null', function () {
    $plan = FloorPlan::factory()->create();
    $table = FloorPlanElement::factory()->create(['floor_plan_id' => $plan->id]);

    $reservedAt = Carbon::parse('2030-01-15 19:00');
    Reservation::factory()->arrived()->create([
        'floor_plan_element_id' => $table->id,
        'reservation_datetime' => $reservedAt,
        'seated_at' => null,
    ]);

    expect(app(ReservationService::class)->getStayEndForElement($table->id)->toDateTimeString())
        ->toBe($reservedAt->copy()->addHours(2)->toDateTimeString());
});

it('caps stay end at the next scheduled reservation when it lands within the 2-hour window', function () {
    $plan = FloorPlan::factory()->create();
    $table = FloorPlanElement::factory()->create(['floor_plan_id' => $plan->id]);

    $reservedAt = Carbon::parse('2030-01-15 19:00');
    $seatedAt = Carbon::parse('2030-01-15 19:00');
    Reservation::factory()->arrived()->create([
        'floor_plan_element_id' => $table->id,
        'reservation_datetime' => $reservedAt,
        'seated_at' => $seatedAt,
    ]);

    $nextAt = $reservedAt->copy()->addMinutes(90);
    Reservation::factory()->scheduled()->create([
        'floor_plan_element_id' => $table->id,
        'reservation_datetime' => $nextAt,
    ]);

    expect(app(ReservationService::class)->getStayEndForElement($table->id)->toDateTimeString())
        ->toBe($nextAt->toDateTimeString());
});

it('records seated_at when seating a reservation', function () {
    $plan = FloorPlan::factory()->create();
    $table = FloorPlanElement::factory()->create(['floor_plan_id' => $plan->id]);

    $reservation = Reservation::factory()->scheduled()->create([
        'floor_plan_element_id' => $table->id,
        'reservation_datetime' => Carbon::parse('2030-01-15 19:00'),
    ]);

    $frozen = Carbon::parse('2030-01-15 19:10');
    Carbon::setTestNow($frozen);

    app(ReservationService::class)->seatReservation($reservation);

    expect($reservation->fresh()->seated_at->toDateTimeString())->toBe($frozen->toDateTimeString());

    Carbon::setTestNow();
});

it('excludes tables where seat count is less than party size', function () {
    $plan = FloorPlan::factory()->create();
    $small = FloorPlanElement::factory()->create([
        'floor_plan_id' => $plan->id,
        'seat_count' => 2,
        'table_name' => 'T3',
    ]);

    $dateTime = Carbon::parse('2030-01-15 19:00');

    $results = app(ReservationService::class)->getAvailableTablesAt($dateTime, 4);

    expect($results->pluck('id'))->not->toContain($small->id);
});
