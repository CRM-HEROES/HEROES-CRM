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
        // A call made by an AI agent on behalf of its owner is not a visit of the owner
        if ($request->attributes->get('agent_internal')) {
            return $next($request);
        }

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
