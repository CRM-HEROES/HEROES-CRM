<?php

namespace App\Utils\Tracker;

use Illuminate\Support\Facades\Cache;

/**
 * The tracker resolves agents, devices, cookies, routes... to ids on every request
 * (a SELECT each). These rows almost never change, so the ids are cached.
 *
 * The cache is an optimisation only: if it is unavailable (e.g. redis down) the
 * lookup silently falls back to the database instead of failing the request.
 */
trait RemembersIds
{
    /**
     * Seconds an id is kept in cache.
     */
    protected int $idCacheTtl = 1800;

    /**
     * @param  string  $key
     * @param  \Closure  $resolve  returns the id when it is not cached
     * @return int
     */
    protected function rememberId(string $key, \Closure $resolve)
    {
        try {
            $id = Cache::get($key);
        } catch (\Throwable $e) {
            return $resolve();
        }

        if ($id) {
            return $id;
        }

        $id = $resolve();

        try {
            Cache::put($key, $id, $this->idCacheTtl);
        } catch (\Throwable $e) {
            // ignored: the id is simply resolved again next time
        }

        return $id;
    }
}
