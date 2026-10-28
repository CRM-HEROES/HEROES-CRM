<?php

namespace App\Providers;

use App\Services\Monitoring\TelemetryStore;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Throwable;

class ServerMonitoringServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TelemetryStore::class, fn ($app) => new TelemetryStore(
            $app['config']->get('server-monitoring.directory', storage_path('app/server-monitoring')),
            (int) $app['config']->get('server-monitoring.http_window', 60),
        ));
    }

    public function boot(): void
    {
        if (!config('server-monitoring.telemetry_enabled', true)) {
            return;
        }
        Event::listen(JobFailed::class, function (JobFailed $event): void {
            try {
                app(TelemetryStore::class)->recordFailedJob(
                    $event->connectionName,
                    (string) $event->job->getQueue(),
                    get_class($event->exception),
                );
            } catch (Throwable $exception) {
                // Failure reporting remains independent from the worker lifecycle.
            }
        });
    }
}
