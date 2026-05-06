<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class OrderCompleted
{
    use Dispatchable;

    public function __construct(
        public int $orderId,
        public string $orderNumber,
        public float $total,
    ) {}
}
