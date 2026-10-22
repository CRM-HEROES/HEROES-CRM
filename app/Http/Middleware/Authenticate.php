<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $this->authenticate($request, $guards);
        $this->pingLastActivity($request, $guards);

        return $next($request);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
    
    /**
     * Seconds between two "last activity" writes for the same user.
     */
    const LAST_ACTIVITY_INTERVAL = 60;

    protected function pingLastActivity(Request $request, $guards)
    {
        if (empty($guards)) {
            $this->touchUser(auth()->user(), $request);
        } else {
            foreach ($guards as $guard) {
                $this->touchUser(auth()->guard($guard)->user(), $request);
            }
        }
    }

    /**
     * Record the last activity and IP address of the user.
     *
     * The ping used to be an Eloquent update on every request. It is now written at most once
     * per interval (activity is displayed with a 5 minutes granularity), except when the IP
     * address changes: that one goes through Eloquent so the observer refreshes the location.
     */
    protected function touchUser($user, Request $request)
    {
        $now = \Carbon\Carbon::now();
        $ip = $request->ip();

        if ($user->ip_address !== $ip) {
            $user->update([
                'last_activity' => $now,
                'ip_address' => $ip,
            ]);

            return;
        }

        if (
            $user->last_activity &&
            \Carbon\Carbon::parse($user->last_activity)->diffInSeconds($now) < static::LAST_ACTIVITY_INTERVAL
        ) {
            return;
        }

        DB::table($user->getTable())
            ->where($user->getKeyName(), $user->getKey())
            ->update(['last_activity' => $now]);

        $user->last_activity = $now;
        $user->syncOriginalAttribute('last_activity');
    }
}
