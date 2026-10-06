<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Server monitoring
    |--------------------------------------------------------------------------
    |
    | Lightweight metrics collection read from /proc (and, optionally, the
    | Docker Engine socket in read-only GET mode). Super admins only.
    |
    */

    // Number of days of history kept in `server_metrics` (1 - 30).
    'retention_days' => max(1, min(30, (int) env('MONITORING_RETENTION_DAYS', 7))),

    // Where to read procfs from. Mount the host's /proc read-only (e.g. on
    // /host/proc) and set this to monitor the host instead of the container.
    'proc_path' => env('MONITORING_PROC_PATH', '/proc'),

    // Path whose filesystem usage is reported.
    'disk_path' => env('MONITORING_DISK_PATH', base_path()),

    // Docker Engine unix socket (mount it read-only). Leave the file absent to
    // disable the container section.
    'docker_socket' => env('MONITORING_DOCKER_SOCKET', '/var/run/docker.sock'),

    // Seconds during which a live snapshot is served from cache, so that
    // polling clients can never trigger more than one collection per window.
    'live_cache_seconds' => (int) env('MONITORING_LIVE_CACHE', 10),

    // Thresholds (%) above which a sample is considered a consumption peak.
    'peak_cpu' => (float) env('MONITORING_PEAK_CPU', 85),
    'peak_ram' => (float) env('MONITORING_PEAK_RAM', 90),

    // Number of processes / containers kept per ranking.
    'top_processes' => 15,
    'top_containers' => 30,
];
