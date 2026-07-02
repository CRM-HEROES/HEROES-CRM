<?php

namespace App\Listeners;

use App\Events\UserDeviceCreated;
use App\Notifications\UserDeviceCreated as NotificationsUserDeviceCreated;
use Illuminate\Support\Facades\DB;

class UserDeviceCreatedListener
{
    /**
    * Handle the event.
    *
    * @param  Login  $event
    * @return void
    */
    public function handle(UserDeviceCreated $event)
    {
        if (auth()->user()) {
            $session = DB::table('tracker_sessions')
                ->select(
                    'tracker_sessions.id', 
                    'tracker_geoip.latitude', 
                    'tracker_geoip.longitude', 
                    'tracker_geoip.country_code', 
                    'tracker_geoip.country_name', 
                    'tracker_geoip.region', 
                    'tracker_geoip.city', 
                    'tracker_geoip.postal_code',
                    'tracker_sessions.created_at', 
                    'tracker_agents.browser',
                    'tracker_agents.browser_version'
                )
                ->join("tracker_geoip", "tracker_sessions.geoip_id", "=", "tracker_geoip.id")
                ->join("tracker_agents", "tracker_sessions.agent_id", "=", "tracker_agents.id")
                ->where('tracker_sessions.user_id', auth()->id())
                ->where('tracker_sessions.device_id', $event->device->id)
                ->orderBy('tracker_sessions.created_at', 'desc')
                ->first();

            auth()->user()
                ->notify(
                  new NotificationsUserDeviceCreated(auth()->user(), $event->device, $session)
              );
        }
    }
}