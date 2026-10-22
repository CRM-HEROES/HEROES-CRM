<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserAuthenticatedListener
{
    /**
     * Seconds the "is this device banned" answer is kept in cache.
     */
    const BAN_CACHE_TTL = 60;

    public function __construct()
    {
        //
    }

    public function handle(Authenticated $event, ...$guards)
    {
        if ($event->user instanceof \App\Models\User) {
            // Only the banned_at column of the banned devices table is needed,
            // and a ban is confirmed/cancelled through SessionController which clears this cache.
            $data = app(\App\Utils\Tracker\Tracker::class)->makeSessionData();
            $bannedAt = $this->deviceBannedAt($data['user_id'], $data['device_id'], $data['client_ip']);

            if ($event->user->banned_at || $bannedAt) {
                \Auth::logout();
            }
        }
    }

    /**
     * Cache key of a device ban status
     */
    public static function banCacheKey($userId, $deviceId, $clientIp): string
    {
        return 'tracker.device.banned.' . md5($userId . '|' . $deviceId . '|' . $clientIp);
    }

    protected function deviceBannedAt($userId, $deviceId, $clientIp)
    {
        $query = function () use ($userId, $deviceId, $clientIp) {
            $device = DB::table('tracker_user_banned_devices')
                ->select('banned_at')
                ->where('user_id', $userId)
                ->where('device_id', $deviceId)
                ->where('client_ip', $clientIp)
                ->first();

            // "" instead of null so that "not banned" is cached too
            return $device && $device->banned_at ? $device->banned_at : '';
        };

        try {
            return Cache::remember(static::banCacheKey($userId, $deviceId, $clientIp), static::BAN_CACHE_TTL, $query);
        } catch (\Throwable $e) {
            return $query();
        }
    }
}
