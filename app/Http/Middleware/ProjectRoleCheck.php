<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProjectRoleCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->project && $request->role) {
            abort_unless(
                $request->project->roles()->find($request->role->id),
                404, 
                trans('project.error.unknown_role')
            );
        }

        return $next($request);
    }
}
