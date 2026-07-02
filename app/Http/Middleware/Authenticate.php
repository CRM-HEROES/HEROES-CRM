<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

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
    
    protected function pingLastActivity(Request $request, $guards)
    {
        if (empty($guards)) {
            auth()->user()->update([
                'last_activity' => \Carbon\Carbon::now(),
                'ip_address' => $request->ip(),
            ]);
        } else {
            foreach ($guards as $guard) {
                auth()->guard($guard)->user()->update([
                    'last_activity' => \Carbon\Carbon::now(),
                    'ip_address' => $request->ip(),
                ]);
            }
        }
    }
}
