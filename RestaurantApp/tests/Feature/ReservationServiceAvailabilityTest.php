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
        'seat_count'    => 4,
        'table_name'    => 'T1',
    ]);

    $dateTime = Carbon::parse('2030-01-15 19:00');

    $results = app(ReservationService::class)->getAvailableTablesAt($dateTime, 2);

    expect($results->pluck('id'))->toContain($table->id);
});

it('excludes tables with an overlapping reservation', function () {
    $plan = FloorPlan::factory()->create();
    $table = FloorPlanElement::factory()->create([
        'floor_plan_id' => $plan->id,
        'seat_count'    => 4,
        'table_name'    => 'T2',
    ]);

    $dateTime = Carbon::parse('2030-01-15 19:00');

    Reservation::factory()->create([
        'floor_plan_element_id' => $table->id,
        'reservation_datetime'  => $dateTime,
        'status'                => 'scheduled',
    ]);

    $results = app(ReservationService::class)->getAvailableTablesAt($dateTime, 2);

    expect($results->pluck('id'))->not->toContain($table->id);
});

it('excludes tables where seat count is less than party size', function () {
    $plan = FloorPlan::factory()->create();
    $small = FloorPlanElement::factory()->create([
        'floor_plan_id' => $plan->id,
        'seat_count'    => 2,
        'table_name'    => 'T3',
    ]);

    $dateTime = Carbon::parse('2030-01-15 19:00');

    $results = app(ReservationService::class)->getAvailableTablesAt($dateTime, 4);

    expect($results->pluck('id'))->not->toContain($small->id);
});

