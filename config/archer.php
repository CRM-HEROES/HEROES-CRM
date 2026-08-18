<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ARCHER nightly enrichment (P6)
    |--------------------------------------------------------------------------
    |
    | Default targeting criteria for the nightly "archer:enrich" command.
    | Every value can be overridden per run via the command's options.
    |
    */

    'batch_size' => env('ARCHER_BATCH_SIZE', 200),

    'min_age' => env('ARCHER_MIN_AGE'),
    'max_age' => env('ARCHER_MAX_AGE'),

    // Comma-separated list of cities/postal code prefixes/states, e.g. "75,92,Paris".
    'zones' => env('ARCHER_ZONES'),

    // Comma-separated list of sectors, e.g. "immobilier,assurance".
    'sectors' => env('ARCHER_SECTORS'),

    'min_appetency' => env('ARCHER_MIN_APPETENCY'),

    // Percentile threshold used by "archer:rank" to flag the head of the
    // assignment queue (top 20% by archer_score).
    'top_percentile' => env('ARCHER_TOP_PERCENTILE', 20),

];
