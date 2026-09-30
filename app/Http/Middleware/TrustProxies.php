<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    /**
     * Optional TRUSTED_PROXIES env variable: "*" or a comma separated list of
     * proxy IPs. Needed behind a tunnel / reverse proxy that terminates HTTPS
     * (ngrok, load balancer), otherwise Laravel sees "http" and redirects to
     * http:// URLs, which browsers block as mixed content.
     * Unset = nobody is trusted (unchanged default).
     */
    protected function proxies()
    {
        $proxies = env('TRUSTED_PROXIES');

        if ($proxies === '*') {
            return '*';
        }

        return $proxies
            ? array_map('trim', explode(',', $proxies))
            : $this->proxies;
    }
}
