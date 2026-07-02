<?php

namespace App\Utils\Tracker;

use Illuminate\Cookie\CookieJar;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid as UUID;

class CookieRepository
{
    protected $model;
    protected $request;
    protected $cookieJar;
    protected $cookieKey;

    public function __construct(Cookie $model, Request $request, CookieJar $cookieJar)
    {
        $this->model = $model;
        $this->request = $request;
        $this->cookieJar = $cookieJar;
        $this->cookieKey = $this->getCookieKey();
    }

    protected function getCookieKey()
    {
        return "tracker_cookie";
    }

    protected function newQuery()
    {
        return $this->model->newQuery();
    }

    public function firstOrCreate($attributes)
    {
        return $this->newQuery()->firstOrCreate($attributes);
    }

    public function getId()
    {
        if (! $cookie = $this->request->cookie($this->cookieKey)) {
            $cookie = (string) UUID::uuid4();

            $this->cookieJar->queue(cookie($this->cookieKey, $cookie, 0));
        }

        return $this->firstOrCreate(['uuid' => $cookie])->id;
    }
}