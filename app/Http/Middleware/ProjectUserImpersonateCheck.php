<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProjectUserImpersonateCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
		if (!auth()->user()) {
    		return $next($request);
		}
		
    	// we'll only check for connected user
    	if (get_class(auth()->user()) !== "App\\Models\\User") {
    		return $next($request);
    	}

    	// if there are no impersonated project session
    	if (!($project = session('impersonate_project'))) {
    		return $next($request);
    	}

    	// if the impersonated project is the same as the current project
    	if ($request->project && $request->project->slug == $project) {
    		return $next($request);
    	}

        if (in_array($request->route()->getName(), ["api.auth", "api.event", "api.event.index", "api.user.impersonate.leave", "api.google.calendar.index", "api.google.authenticator.check", "api.user.email"])) {
    		return $next($request);
        }

        return abort(404, 'Resource not found');
    }
}
