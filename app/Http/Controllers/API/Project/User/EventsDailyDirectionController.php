<?php

namespace App\Http\Controllers\API\Project\User;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Services\EventsDailyDirections;

class EventsDailyDirectionController extends Controller
{
    public function index(Project $project, User $user, $date, EventsDailyDirections $directions)
    {
        validator(['date' => $date], ['date' => ['required', 'date_format:Y-m-d']])->validate();

        // The underlying itinerary is intentionally shared by user/date across projects.
        $result = $directions->request($user->id, $date);

        return response()->json($result, $result['status'] === 'pending' ? 202 : 200);
    }
}
