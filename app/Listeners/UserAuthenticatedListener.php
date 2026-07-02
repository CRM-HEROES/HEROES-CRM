<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Facades\DB;

class UserAuthenticatedListener
{
    /**
    * Create the event listener.
    *
    * @return void
    */
    public function __construct()
    {
        //
    }

    /**
    * Handle the event.
    *
    * @param  Login  $event
    * @return void
    */
    public function handle(Authenticated $event, ...$guards)
    {
        if ($event->user instanceof \App\Models\User) {
            $data = app(\App\Utils\Tracker\Tracker::class)->makeSessionData();
            $device = DB::table('tracker_user_banned_devices')
                ->select('banned_at')
                ->where('user_id', $data['user_id'])
                ->where('device_id', $data['device_id'])
                ->where('client_ip', $data['client_ip'])
                ->first();

            if ($event->user->banned_at || ($device && $device->banned_at)) {
                \Auth::logout();
            }
        }
    }
}