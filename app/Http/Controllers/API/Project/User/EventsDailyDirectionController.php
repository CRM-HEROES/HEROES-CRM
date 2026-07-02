<?php

namespace App\Http\Controllers\API\Project\User;

use App\Http\Controllers\Controller;
use App\Jobs\UserGetEventsDailyDirection;
use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventsDailyDirectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Project $project, User $user, $date)
    {
        // $attempt = 0;
    	// do {
        $direction = $this->getUserEventDailyDirection($project, $user, $date);

        if (!$direction) {
            dispatch((new UserGetEventsDailyDirection(/*$project, */$user, $date))->onConnection('sync'));
            $direction = $this->getUserEventDailyDirection($project, $user, $date);
        }

			// $continue = false; /*$direction && $direction->searching && $attempt <= 1;*/

			/*if ($continue) {
				sleep(1);
			}

            $attempt++;
		} while ($continue);*/

		return $direction;
    }

    protected function getUserEventDailyDirection(Project $project, User $user, $date)
    {
        return DB::table('user_events_daily_directions')
            // ->where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->where('direction_at', $date)
            ->first();
    }
}
