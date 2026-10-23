<?php

return [
    // Set TRAIN_DEMO_DAYS_AHEAD in the process environment to change the demo lead time.
    'demo_days_ahead' => max(1, min(30, (int) env('TRAIN_DEMO_DAYS_AHEAD', 7))),

    // Keep retired fictional catalog records available to booking history, but out of new searches.
    'legacy_search_excluded_station_codes' => ['SSDEMO01', 'SSDEMO02', 'SSDEMO03', 'SSDEMO04'],
    'legacy_search_excluded_train_codes' => ['SS-DEMO-01', 'SS-DEMO-02'],
];
