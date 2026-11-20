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
        $project = $request->route('project');
        $user = $request->route('user');

        if ($project && $user) {
            abort_unless(
                $user->is_super_admin ||
                DB::table('user_project')->where(['project_id' => $project->id, 'user_id' => $user->id])->first(),
                404, 
                trans('project.error.unknown_user')
            );
        }

        return $next($request);
    }
}
