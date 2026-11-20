<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UserLastProjectCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        $project = $request->route('project');

        if ($user) {
            if ($project instanceof Project) {
                if ($user->last_project_id != $project->id) {
                    $user->update([
                        'last_project_id' => $project->id
                    ]);
                    DB::table('user_project')->where([
                        'user_id' => $user->id,
                        'project_id' => $project->id,
                    ])->update([
                        'last_activity' => \Carbon\Carbon::now()
                    ]);
                }
            } else if ($user->last_project_id) {
                $user->update([
                    'last_project_id' => null
                ]);
            } 
        }

        return $next($request);
    }
}
