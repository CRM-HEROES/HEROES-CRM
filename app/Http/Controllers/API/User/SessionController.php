<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\UserDeviceBan;
use App\Utils\Tracker\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(User $user)
    {
        return DB::table('tracker_sessions')
            ->select(
                'tracker_sessions.id', 
                'tracker_devices.kind', 
                'tracker_devices.model', 
                'tracker_devices.platform', 
                'tracker_devices.platform_version', 
                'tracker_geoip.latitude', 
                'tracker_geoip.longitude', 
                'tracker_geoip.country_code', 
                'tracker_geoip.country_name', 
                'tracker_geoip.region', 
                'tracker_geoip.city', 
                'tracker_geoip.postal_code',
                'tracker_sessions.created_at', 
                'tracker_agents.browser',
                'tracker_agents.browser_version',
                'tracker_user_banned_devices.banned_at'
            )
            ->join("tracker_devices", "tracker_sessions.device_id", "=", "tracker_devices.id")
            ->join("tracker_geoip", "tracker_sessions.geoip_id", "=", "tracker_geoip.id")
            ->join("tracker_agents", "tracker_sessions.agent_id", "=", "tracker_agents.id")
            ->leftJoin('tracker_user_banned_devices', function($join) {
                $join->on('tracker_user_banned_devices.user_id', '=', 'tracker_sessions.user_id');
                $join->on('tracker_user_banned_devices.device_id', '=', 'tracker_sessions.device_id');
                $join->on('tracker_user_banned_devices.client_ip', '=', 'tracker_sessions.client_ip');
            })
            ->where('tracker_sessions.user_id', $user->id)
            ->orderBy('tracker_sessions.created_at', 'desc')
            ->get();
    }

    /**
     * Get history of geoip
     */
    public function geoip(User $user)
    {
        $geoips = DB::table('tracker_sessions')
            ->select(
                'tracker_sessions.id', 
                'tracker_sessions.client_ip', 
                'tracker_geoip.latitude', 
                'tracker_geoip.longitude', 
                'tracker_geoip.country_name', 
                'tracker_geoip.city', 
                'tracker_geoip.postal_code',
                'tracker_sessions.created_at', 
            )
            ->join("tracker_geoip", "tracker_sessions.geoip_id", "=", "tracker_geoip.id")
            ->where('tracker_sessions.user_id', $user->id)
            ->orderBy('tracker_sessions.created_at', 'desc')
            ->get()
            ->toArray();

        if (count($geoips) == 0) {
            return [];
        }

        $result = [];
        $current = [
            "ip" => $geoips[0]->client_ip,
            "latitude" => $geoips[0]->latitude,
            "longitude" => $geoips[0]->longitude,
            "city" => $geoips[0]->city,
            "country_name" => $geoips[0]->country_name,
            "postal_code" => $geoips[0]->postal_code,
            "to" => null,
        ];

        foreach ($geoips as $geoip) {
            if ($geoip->latitude != $current["latitude"] || $geoip->longitude != $current["longitude"]) {
                $result[] = $current;

                $current = [
                    "ip" => $geoip->client_ip,
                    "latitude" => $geoip->latitude,
                    "longitude" => $geoip->longitude,
                    "city" => $geoip->city,
                    "country_name" => $geoip->country_name,
                    "postal_code" => $geoip->postal_code,
                    "to" => $geoip->created_at,
                ];
            }
        }

        $result[] = $current;

        return $result;
    }

    public function ban(User $user, Session $session)
    {
    	$code = rand(100000, 999999);

        DB::table('tracker_user_banned_devices')->updateOrInsert([
            'user_id' => $user->id,
            'device_id' => $session->device_id,
            'client_ip' => $session->client_ip
        ], [
            'ban_code' => $code,
        ]);

        auth()->user()->notify(new UserDeviceBan(auth()->user(), $session, $code));
    }

    public function confirmBan(Request $request, User $user, Session $session)
    {
        $this->validate($request, [
            'code' => 'required'
        ]);

        $device = DB::table('tracker_user_banned_devices')
            ->select('ban_code')
            ->where('user_id', $user->id)
            ->where('device_id', $session->device_id)
            ->where('client_ip', $session->client_ip)
            ->first();

        abort_unless($device && $device->ban_code == $request->input('code'), 404);

        DB::table('tracker_user_banned_devices')
            ->where('user_id', $user->id)
            ->where('device_id', $session->device_id)
            ->where('client_ip', $session->client_ip)
            ->update(['banned_at' => \Carbon\Carbon::now()]);
    }

    public function cancelBan(User $user, Session $session)
    {
        DB::table('tracker_user_banned_devices')
            ->where('user_id', $user->id)
            ->where('device_id', $session->device_id)
            ->where('client_ip', $session->client_ip)
            ->delete();
    }
}
