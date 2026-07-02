<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TrackerStatController extends Controller
{
    public function show(User $user)
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
            ->get();

        return $connections->count() > 0 ? $connections->reduce(function($carry, $connection) {
            $value = json_decode($connection->hits_per_time);

            if (empty($carry)) {
                return $value;
            }

            $resultArray = [];
            $length = count($carry);

            for ($i = 0; $i < $length; $i++) {
                $resultArray[] = $carry[$i] + $value[$i];
            }

            return $resultArray;
        }, null) : [];
    }
}
