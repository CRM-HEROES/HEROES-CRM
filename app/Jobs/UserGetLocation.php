<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Stevebauman\Location\Facades\Location;

/**
 * Resolves the location of a user's IP address (external HTTP call) outside of the
 * request that detected the IP change.
 */
class UserGetLocation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $tries = 2;
    public $timeout = 30;

    protected int $userId;
    protected string $ip;

    public function __construct(int $userId, string $ip)
    {
        $this->userId = $userId;
        $this->ip = $ip;
    }

    public function handle(): void
    {
        $address = Location::get($this->ip);

        if (!$address) {
            return;
        }

        // The IP may have changed again while the job was waiting
        $user = DB::table('users')->where('id', $this->userId)->first(['ip_address']);
        if (!$user || $user->ip_address !== $this->ip) {
            return;
        }

        // Truncated to the column sizes: an overlong value must not make the update fail
        DB::table('users')
            ->where('id', $this->userId)
            ->update([
                'ip_country' => $this->limit($address->countryName, 191),
                'ip_state' => $this->limit($address->regionName, 191),
                'ip_city' => $this->limit($address->cityName, 191),
                'ip_postal_code' => $this->limit($address->postalCode ? $address->postalCode : $address->zipCode, 20),
                'ip_latitude' => $address->latitude,
                'ip_longitude' => $address->longitude,
            ]);
    }

    protected function limit($value, int $length)
    {
        return $value === null ? null : Str::limit((string) $value, $length, '');
    }
}
