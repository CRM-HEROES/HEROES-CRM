<?php

namespace App\Http\Middleware;

use App\Services\Monitoring\TelemetryStore;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RecordServerMonitoringRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('server-monitoring.telemetry_enabled', true) && !$request->is('api/server-monitoring', 'api/server-monitoring/*')) {
            $request->attributes->set('_monitoring_started', microtime(true));
        }
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        try {
            $started = $request->attributes->get('_monitoring_started');
            if (!$started) {
                return;
            }
            // Route templates avoid storing IDs, personal information or URL tokens.
            $route = $request->route();
            $uri = is_object($route) && method_exists($route, 'uri') ? $route->uri() : '[sans route]';
            app(TelemetryStore::class)->recordRequest($request->method(), $uri, $response->getStatusCode(), (microtime(true) - $started) * 1000);
        } catch (Throwable $exception) {
            // Do not report telemetry failures recursively into application logs.
        }
    }
}
