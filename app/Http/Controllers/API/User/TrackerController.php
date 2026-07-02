<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Utils\Tracker\Log;
use Illuminate\Http\Request;

class TrackerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, User $user)
    {
        return Log::with('session', 'routePath', 'session.agent', 'session.geoIp')
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$request->input('start'), $request->input('end')])
            ->get();
    }
}
