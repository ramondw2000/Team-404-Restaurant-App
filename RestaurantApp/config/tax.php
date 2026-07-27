<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Sales Tax Rate
    |--------------------------------------------------------------------------
    |
    | Decimal rate applied on top of the pre-tax subtotal for orders,
    | receipts and statistics. Drive it through the TAX_RATE env variable
    | so every surface (orders, receipts, statistics) stays in sync.
    |
    */

    'rate' => (float) env('TAX_RATE', 0.10),

];
