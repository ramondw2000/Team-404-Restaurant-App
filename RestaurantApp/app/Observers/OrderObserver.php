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
     * When a draft/active order is created or updated, ensure table is marked Occupied.
     * Note: Table does NOT revert to Available when order is completed - only when guest departs.
     */
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        // Only mark table as Occupied when an active order is created
        // Table stays Occupied even when order is completed - guest must depart to free table
        if ($order->status->isActive()) {
            $order->floorPlanElement()->update(['status' => TableStatus::Occupied]);
        }
    }
}
