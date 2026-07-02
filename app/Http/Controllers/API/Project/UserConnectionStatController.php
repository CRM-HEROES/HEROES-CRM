<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class UserConnectionStatController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function index(Project $project)
    {
        abort_unless(auth()->user()->is_super_admin, 404);

        $connections = DB::table('tracker_logs')
          ->select(
            DB::raw("COUNT(DISTINCT(tracker_logs.user_id)) AS connection, DATE_FORMAT(tracker_logs.created_at, '%Y-%m') AS month")
          )
          ->join('tracker_route_paths', 'tracker_route_paths.id', '=', 'tracker_logs.route_path_id')
          ->where('tracker_route_paths.path', 'LIKE', 'api/project/' . $project->slug . '/%')
          ->groupBy('month')
          ->orderBy('month', 'desc')
          ->get();

      return $connections;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project, $date)
    {
        abort_unless(auth()->user()->is_super_admin, 404);

        $connections = DB::table('tracker_logs')
          ->select(
            DB::raw("DISTINCT(tracker_logs.user_id) AS user_id, users.name AS name")
          )
          ->join('tracker_route_paths', 'tracker_route_paths.id', '=', 'tracker_logs.route_path_id')
          ->join('users', 'users.id', '=', 'tracker_logs.user_id')
          ->where('tracker_route_paths.path', 'LIKE', 'api/project/' . $project->slug . '/%')
          ->where(DB::raw("DATE_FORMAT(tracker_logs.created_at, '%Y-%m')"), $date)
          ->get();

      return $connections;
    }
}
