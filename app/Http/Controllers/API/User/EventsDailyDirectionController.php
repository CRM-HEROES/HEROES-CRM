<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Jobs\UserGetEventsDailyDirection;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EventsDailyDirectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(User $user, $date)
    {
        // $attempt = 0;
    	// do {
        $direction = $this->getUserEventDailyDirection($user, $date);

        if (!$direction) {
            dispatch((new UserGetEventsDailyDirection(/*$project, */$user, $date))->onConnection('sync'));
            $direction = $this->getUserEventDailyDirection($user, $date);
        }

		return $direction;
    }

    protected function getUserEventDailyDirection(User $user, $date)
    {
        return DB::table('user_events_daily_directions')
            ->where('user_id', $user->id)
            ->where('direction_at', $date)
            ->first();
    }
}
