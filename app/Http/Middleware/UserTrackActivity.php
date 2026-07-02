<?php

namespace App\Http\Middleware;

use Closure;

class UserTrackActivity
{
    protected $request;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /*if (
            $request->cookie(config('cookie-consent.cookie_name')) && 
            !$request->is(config('prospect.prefix', "client")) && 
            !$request->is(config('prospect.prefix', "client") . "/*")
        ) */{
            app(\App\Utils\Tracker\Tracker::class)->boot();
        }

        return $next($request);
    }
}
