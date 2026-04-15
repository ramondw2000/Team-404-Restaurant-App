<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Models\Order;

class OrderObserver
{
    /**
     * When a draft order is created, mark the table as Occupied immediately.
     */
    public function created(Order $order): void
    {
        if ($order->status->isActive()) {
            $order->floorPlanElement()->update(['status' => TableStatus::Occupied]);
        }
    }

    /**
     * When an order status changes, update the table status accordingly.
     */
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        if ($order->status->isActive()) {
            $order->floorPlanElement()->update(['status' => TableStatus::Occupied]);

            return;
        }

        if ($order->status === OrderStatus::Completed || $order->status === OrderStatus::Cancelled) {
            $hasOtherActiveOrder = Order::where('floor_plan_element_id', $order->floor_plan_element_id)
                ->where('id', '!=', $order->id)
                ->whereIn('status', [OrderStatus::Draft, OrderStatus::Active])
                ->exists();

            $hasSeatedReservation = $order->floorPlanElement
                ?->reservations()
                ->whereIn('status', ['arrived', 'scheduled'])
                ->whereDate('reservation_datetime', today())
                ->exists();

            if (! $hasOtherActiveOrder && ! $hasSeatedReservation) {
                $order->floorPlanElement()->update(['status' => TableStatus::Available]);
            }
        }
    }
}
