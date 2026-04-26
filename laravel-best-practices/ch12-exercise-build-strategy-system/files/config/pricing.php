<?php

return [
    'driver' => env('PRICING_STRATEGY', 'none'),
    'fixed_off_pence' => (int) env('PRICING_FIXED_OFF_PENCE', 500),
    'percent_off' => (int) env('PRICING_PERCENT_OFF', 10),
];
