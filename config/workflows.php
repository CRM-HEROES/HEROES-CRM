<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    |
    | Queue of the workflow jobs. The workers (supervisor/worker.conf) must
    | listen to it, otherwise no workflow will ever advance.
    |
    */

    'queue' => env('WORKFLOW_QUEUE', 'workflows'),

    /*
    |--------------------------------------------------------------------------
    | Engine limits
    |--------------------------------------------------------------------------
    */

    // Nodes executed by one job before it hands over to a new one
    'max_steps_per_run' => 50,

    // A "running" execution that was not released after this delay
    // (killed worker) is picked up again by workflows:resume
    'stuck_after_minutes' => 15,

    // Executions dispatched at most by one workflows:resume run
    'sweep_limit' => 2000,

    // Longest wait a node can ask for
    'max_wait_days' => 365,

    // Days the history of the executions that are over is kept
    // (twice as long for the failed ones): see workflows:prune
    'retention_days' => (int) env('WORKFLOW_RETENTION_DAYS', 90),

    /*
    |--------------------------------------------------------------------------
    | Rate limits
    |--------------------------------------------------------------------------
    |
    | Communications per minute and per operator of a project. Beyond it the
    | step is postponed (never dropped, never sent twice). Adjust them to the
    | limits of the operators; 0 disables the limit.
    |
    */

    'rate_limits' => [
        'email' => (int) env('WORKFLOW_RATE_EMAIL', 120),
        'sms' => (int) env('WORKFLOW_RATE_SMS', 60),
        'call' => (int) env('WORKFLOW_RATE_CALL', 20),
    ],

    /*
    |--------------------------------------------------------------------------
    | Triggers
    |--------------------------------------------------------------------------
    |
    | "<subject>.<event>": the part before the dot is the entity the workflow
    | runs on (prospect, order or event).
    |
    */

    'triggers' => [
        'prospect.created',
        'prospect.updated',
        'prospect.filter_entered',
        'prospect.filter_left',
        'order.created',
        'order.updated',
        'event.created',
        'event.updated',
        'event.starting_soon',
        'event.ended',
    ],

];
