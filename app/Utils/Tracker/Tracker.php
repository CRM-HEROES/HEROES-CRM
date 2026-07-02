<?php

namespace App\Utils\Tracker;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;

class Tracker
{

    protected $agentRepository;
    protected $cookieRepository;
    protected $deviceRepository;
    protected $geoIpRepository;
    protected $logRepository;
    protected $routePathRepository;
    protected $sessionRepository;
    protected $request;
    protected $route;
    protected $app;
    protected $sessionData;

    public function __construct(
            AgentRepository $agentRepository,
            CookieRepository $cookieRepository,
            DeviceRepository $deviceRepository,
            GeoIpRepository $geoIpRepository,
            LogRepository $logRepository,
            RoutePathRepository $routePathRepository,
            SessionRepository $sessionRepository,
            Request $request,
            Router $route,
            Application $app)
    {
        $this->agentRepository = $agentRepository;
        $this->cookieRepository = $cookieRepository;
        $this->deviceRepository = $deviceRepository;
        $this->geoIpRepository = $geoIpRepository;
        $this->logRepository = $logRepository;
        $this->routePathRepository = $routePathRepository;
        $this->sessionRepository = $sessionRepository;
        $this->request = $request;
        $this->route = $route;
        $this->app = $app;
    }

    public function allSessions()
    {
        return $this->sessionRepository->all();
    }

    public function boot()
    {
        $this->track();
    }

    protected function track()
    {
        $log = $this->getLogData();

        $this->logRepository->create($log);
    }

    protected function getLogData()
    {
        return [
            'user_id'    => $this->getUserId(),
            'session_id' => $this->getSessionId(),
            'route_path_id' => $this->getRoutePathId(),
            'method'     => $this->request->method(),
            'is_ajax'    => $this->request->ajax(),
            'is_secure'  => $this->request->isSecure(),
            'is_json'    => $this->request->isJson(),
            'wants_json' => $this->request->wantsJson(),
        ];
    }

    protected function getAgentId()
    {
        return $this->agentRepository->getId();
    }

    protected function getCookieId()
    {
        return $this->cookieRepository->getId();
    }

    protected function getDeviceId()
    {
        return $this->deviceRepository->getId();
    }

    protected function getGeoIpId()
    {
        return $this->geoIpRepository->getId($this->request->getClientIp());
    }

    protected function getRoutePathId()
    {
        return $this->routePathRepository->getId($this->route, $this->request);
    }

    protected function getSessionId()
    {
        return $this->sessionRepository->getId($this->makeSessionData());
    }

    public function makeSessionData()
    {
        $sessionData = [
            'user_id'      => $this->getUserId(),
            'device_id'    => $this->getDeviceId(),
            'client_ip'    => $this->request->getClientIp(),
            'geoip_id'     => $this->getGeoIpId(),
            'agent_id'     => $this->getAgentId(),
            'cookie_id'    => $this->getCookieId(),
        ];

        return $sessionData;
    }

    protected function getUserId()
    {
        return auth()->id();
    }
}