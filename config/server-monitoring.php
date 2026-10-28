<?php

return [
    // Kept on the shared local filesystem: monitoring never queries the CRM database.
    'directory' => storage_path('app/server-monitoring'),
    'telemetry_enabled' => env('SERVER_MONITORING_ENABLED', true),
    'interval' => 10,
    'stale_after' => 40,
    'http_window' => 60,
    'host_proc' => env('MONITORING_HOST_PROC', '/host/proc'),
    'host_sys' => env('MONITORING_HOST_SYS', '/host/sys'),
    'host_root' => env('MONITORING_HOST_ROOT', '/host/root'),
    'docker_socket' => env('MONITORING_DOCKER_SOCKET', '/var/run/docker.sock'),
    'docker_project' => env('MONITORING_DOCKER_PROJECT'),
    'docker_max_containers' => 30,
    'docker_timeout' => 2.0,
    'docker_budget_seconds' => 3.0,
    'expected_containers' => ['heroescrm', 'nginx', 'redis-heroes', 'supervisor'],
    'docker_exclude_images' => '/(?:^|[\\/._-])(?:mysql|mariadb|postgres|postgresql|mongo|mssql|percona|clickhouse|cassandra|cockroachdb)(?:[\\/:._-]|$)/i',
    'thresholds' => ['cpu' => 85, 'memory' => 90, 'disk' => 90],
];
