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
     * When an order transitions to completed or cancelled, revert the table to Available.
     * When a draft/active order is created (status updated), mark table Occupied.
     */
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        if ($order->status === OrderStatus::Completed || $order->status === OrderStatus::Cancelled) {
            $order->floorPlanElement()->update(['status' => TableStatus::Available]);
        } elseif ($order->status->isActive()) {
            $order->floorPlanElement()->update(['status' => TableStatus::Occupied]);
        }
    }
}
