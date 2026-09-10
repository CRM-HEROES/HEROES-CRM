<?php

namespace App\Http\Middleware;

use App\Models\Project;
use App\Models\Prospect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ProjectProspectCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $project = $request->project;
        $prospect = $request->prospect;

        if ($project && !is_object($project)) {
            $project = Project::where((new Project())->getRouteKeyName(), $project)->first();
            $request->route()->setParameter('project', $project);
        }

        if ($prospect && !is_object($prospect)) {
            $prospect = Prospect::find($prospect);
            $request->route()->setParameter('prospect', $prospect);
        }

        if ($request->project && $request->prospect) {

            // Check if prospect is
            // associated to the current project
            abort_unless(
                $project->id == $prospect->project_id,
                404, 
                trans('project.error.unknown_prospect')
            );

            // Check if prospect is already processed
            // if we are doing update/store/remove
            // to the prospect items
            abort_unless(
                $request->method() == "GET" || !$prospect->processed,
                404, 
                trans('prospect.error.processed')
            );

            // Check if prospect is associated to the
            // current user
            abort_unless(
                /*auth()->user()->can("project.{$request->project->id}") || */
                !auth()->user() ||
                auth()->user()->is_super_admin ||
                auth()->user()->is_project_admin ||
                $prospect->creator_id == auth()->id() ||
                DB::table('prospect_user')
                    ->where(['prospect_id' => $prospect->id, 'user_id' => auth()->id()])
                    ->first() ||
                DB::table('prospect_group')
                    ->where('prospect_id', $prospect->id)
                    ->whereIn('group_id', auth()->user()->groups()->pluck('id'))
                    ->first(), 
                404, 
                trans('prospect.error.unauthorized_user')
            );
        }

        return $next($request);
    }
}
