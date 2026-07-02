<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ProjectUserCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->project && $request->user) {
            abort_unless(
                $request->user->is_super_admin || 
                DB::table('user_project')->where(['project_id' => $request->project->id, 'user_id' => $request->user->id])->first(),
                404, 
                trans('project.error.unknown_user')
            );
        }

        return $next($request);
    }
}
