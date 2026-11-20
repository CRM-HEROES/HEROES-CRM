<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ProjectUserAccessCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $project = $request->route('project');

        if ($project instanceof Project && auth()->user()) {
            abort_unless(
                /*auth()->user()->can("project.{$project->id}") || */
                auth()->user()->is_super_admin || DB::table('user_project')->where(['project_id' => $project->id, 'user_id' => auth()->id()])->first(),
                404, 
                trans('project.error.unauthorized_user')
            );

            setPermissionsTeamId($project->id);
            auth()->user()->load('roles');

            /*if ($project->id != Session::get('current_project')) {
                Session::set('current_project', $project->id);
                auth()->user()->unsetRelation('roles','permissions');
            }*/
        }

        return $next($request);
    }
}
