<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EventsDailyDirections;

class EventsDailyDirectionController extends Controller
{
    public function index(User $user, $date, EventsDailyDirections $directions)
    {
        validator(['date' => $date], ['date' => ['required', 'date_format:Y-m-d']])->validate();

        $result = $directions->request($user->id, $date);

        return response()->json($result, $result['status'] === 'pending' ? 202 : 200);
    }

    public function status(User $user, $date, EventsDailyDirections $directions)
    {
        validator(['date' => $date], ['date' => ['required', 'date_format:Y-m-d']])->validate();

        return response()->json($directions->status($user->id, $date));
    }
}
