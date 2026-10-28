<?php

namespace App\Console\Commands;

use App\Services\Monitoring\SystemCollector;
use Illuminate\Console\Command;
use Throwable;

class CollectServerMonitoring extends Command
{
    protected $signature = 'monitoring:collect {--loop : Collecter en continu toutes les dix secondes}';
    protected $description = 'Publie les métriques réelles de l’hôte et des conteneurs Docker sans accès base de données';

    public function handle(SystemCollector $collector): int
    {
        $directory = config('server-monitoring.directory');
        if (!is_dir($directory) && !@mkdir($directory, 0770, true) && !is_dir($directory)) {
            $this->error('Répertoire de monitoring inaccessible.');

            return self::FAILURE;
        }
        $lock = @fopen($directory.'/collector.lock', 'c');
        @chmod($directory.'/collector.lock', 0660);
        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
            $this->error('Un collecteur est déjà actif ou le verrou est inaccessible.');

            return self::FAILURE;
        }
        $running = true;
        if (function_exists('pcntl_async_signals')) {
            pcntl_async_signals(true);
            pcntl_signal(SIGTERM, function () use (&$running) { $running = false; });
            pcntl_signal(SIGINT, function () use (&$running) { $running = false; });
        }
        $interval = max(1, (int) config('server-monitoring.interval', 10));
        try {
            do {
                $started = microtime(true);
                try {
                    $collector->collectAndStore();
                } catch (Throwable $exception) {
                    $this->error('Échec de collecte : '.$exception->getMessage());
                    if (!$this->option('loop')) {
                        return self::FAILURE;
                    }
                }
                if (!$this->option('loop')) {
                    $this->info('Métriques publiées.');
                    break;
                }
                while ($running && microtime(true) - $started < $interval) {
                    usleep(100000);
                }
            } while ($running);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }

        return self::SUCCESS;
    }
}
