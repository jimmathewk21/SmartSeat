<?php

return [
    'railway_timezone' => 'Asia/Kolkata',
    'intelligence' => [
        'capture_waitlist_history' => true,
        'min_training_rows' => 200,
        'min_validation_rows' => 100,
        'min_class_rows' => 20,
        'maturity_hours' => 24,
        'max_rows' => 10000,
        'max_model_days' => 90,
        'max_calibration_error' => 0.10,
    ],
    'booking' => [
        'max_passengers' => 6,
        'draft_minutes' => 30,
        'max_drafts' => 10,
        'transaction_attempts' => 3,
    ],
    'administrator_bootstrap' => [
        // Explicit suffixes only, e.g. 'testing'; never use wildcards here.
        'permitted_database_suffixes' => [],
    ],
];
