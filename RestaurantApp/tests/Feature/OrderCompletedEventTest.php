<?php

use App\Events\OrderCompleted;
use App\Models\Order;
use App\Models\Reservation;
use App\Services\OrderService;
use Illuminate\Support\Facades\Event;

test('markUnpaidOrdersPaidForElement fires OrderCompleted with Order model', function () {
    Event::fake([OrderCompleted::class]);

    $reservation = Reservation::factory()->scheduled()->create();
    $order = Order::factory()->active()->create([
        'paid' => false,
        'reservation_id' => $reservation->id,
    ]);

    app(OrderService::class)->markUnpaidOrdersPaidForElement($order->floor_plan_element_id);

    Event::assertDispatched(OrderCompleted::class, function (OrderCompleted $event) use ($order) {
        return $event->order->id === $order->id;
    });
});

test('completeOrder fires OrderCompleted with Order model', function () {
    Event::fake([OrderCompleted::class]);

    $order = Order::factory()->active()->create();

    app(OrderService::class)->completeOrder($order->id);

    Event::assertDispatched(OrderCompleted::class, function (OrderCompleted $event) use ($order) {
        return $event->order->id === $order->id;
    });
});
