<?php

namespace App\Http\Controllers\API\Project\User;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TrackerStatController extends Controller
{
    public function show(Project $project, User $user)
    {
        $date = request()->filled('date') ? 
            Carbon::parse(request('date'))->startOfDay() : 
            Carbon::today()->startOfDay();

        if ($date > Carbon::today()) {
            return [];
        }

        $connections = DB::table('tracker_stats')
            ->where('user_id', $user->id)
            ->where('date', $date->toDateString())
            ->where('project_id', $project->id)
            ->first();

        return $connections ? $connections->hits_per_time : [];
    }
}
