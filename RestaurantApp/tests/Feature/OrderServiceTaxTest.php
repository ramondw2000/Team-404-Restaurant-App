<?php

use App\Enums\OrderStatus;
use App\Models\FloorPlanElement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Reservation;
use App\Services\OrderService;

it('applies the configured tax rate to unpaid order summary', function () {
    config()->set('tax.rate', 0.10);

    $table = FloorPlanElement::factory()->create(['seat_count' => 4, 'table_name' => 'T-tax']);
    $reservation = Reservation::factory()->scheduled()->create([
        'floor_plan_element_id' => $table->id,
        'reservation_datetime' => now(),
    ]);

    $order = Order::factory()->create([
        'floor_plan_element_id' => $table->id,
        'reservation_id' => $reservation->id,
        'status' => OrderStatus::Active,
        'paid' => false,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'quantity' => 2,
        'unit_price' => 25.00,
        'notes' => null,
    ]);

    $summary = app(OrderService::class)->getUnpaidOrderSummaryForElement($table->id);

    expect($summary['subtotal'])->toBe(50.00)
        ->and($summary['tax'])->toBe(5.00)
        ->and($summary['total'])->toBe(55.00);
});

it('scales tax with a different configured rate', function () {
    config()->set('tax.rate', 0.21);

    $table = FloorPlanElement::factory()->create(['seat_count' => 2, 'table_name' => 'T-vat']);
    $reservation = Reservation::factory()->scheduled()->create([
        'floor_plan_element_id' => $table->id,
        'reservation_datetime' => now(),
    ]);

    $order = Order::factory()->create([
        'floor_plan_element_id' => $table->id,
        'reservation_id' => $reservation->id,
        'status' => OrderStatus::Active,
        'paid' => false,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'quantity' => 1,
        'unit_price' => 100.00,
        'notes' => null,
    ]);

    $summary = app(OrderService::class)->getUnpaidOrderSummaryForElement($table->id);

    expect($summary['subtotal'])->toBe(100.00)
        ->and($summary['tax'])->toBe(21.00)
        ->and($summary['total'])->toBe(121.00);
});
