<?php

namespace App\Utils\Tracker;

use Carbon\Carbon;
use Ramsey\Uuid\Uuid as UUID;
use Illuminate\Support\Facades\DB;

class SessionRepository
{
    protected $model;

    protected $relations = ['agent', 'cookie', 'device', 'geoip', 'log', 'language', 'referer', 'user'];

    protected $sessionKey;
    protected $session;
    protected $sessionInfo;

    public function __construct(Session $model)
    {
        $this->model = $model;
        $this->sessionKey = $this->getSessionKey();
        $this->session = session();
    }

    protected function getSessionKey()
    {
        return "tracker_session";
    }

    public function getId($sessionData)
    {
        return $this->getCurrentId($sessionData);
    }

    protected function getCurrentId($sessionInfo)
    {
        $this->setSessionData($sessionInfo);

        return $this->sessionGetId($sessionInfo);
    }

    protected function newQuery()
    {
        return $this->model->newQuery();
    }

    public function find($id)
    {
        return $this->newQuery()->find($id);
    }

    public function findByUuid($uuid)
    {
        return $this->newQuery()->where('uuid', $uuid)->first();
    }

    public function firstOrCreate($attributes)
    {
        return $this->newQuery()->firstOrCreate($attributes);
    }

    protected function getSessions()
    {
        return $this
                ->newQuery()
                ->with($this->relations)
                ->orderBy('updated_at', 'desc');
    }

    public function all()
    {
        return $this->getSessions()->get();
    }

    protected function getSessionData($variable = null)
    {
        $data = $this->session->get($this->sessionKey);

        return data_get($data, $variable, null);
    }

    protected function setSessionData($sessionInfo)
    {
        $this->generateSession($sessionInfo);

        if ($this->createSessionIfNew()) {
            $this->ensureSessionDataIsComplete();
        }
    }

    protected function generateSession($sessionInfo)
    {
        $this->sessionInfo = $sessionInfo;

        if (! $this->sessionIsReliable()) {
            $this->regenerateSystemSession();
        }

        $this->checkSessionUuid();
    }

    protected function checkSessionUuid()
    {
        if (! isset($this->sessionInfo['uuid']) || ! $this->sessionInfo['uuid']) {
            $this->sessionInfo['uuid'] = $this->getSystemSessionId();
        }
    }

    protected function sessionIsReliable()
    {
        $data = $this->getSessionData();

        if ($data && array_key_exists('user_id', $data) && data_get($data, 'user_id') != $this->sessionInfo['user_id']) {
            return false;
        }

        if ($data && array_key_exists('client_ip', $data) && data_get($data, 'client_ip') != $this->sessionInfo['client_ip']) {
            return false;
        }

        if ($data && array_key_exists('user_agent', $data) && data_get($data, 'user_agent') != $this->sessionInfo['user_agent']) {
            return false;
        }

        return true;
    }

    protected function sessionIsKnown()
    {
        if (! $this->session->has($this->sessionKey)) {
            return false;
        }

        if (! $this->getSessionData('uuid') == $this->getSystemSessionId()) {
            return false;
        }

        if (! $this->findByUuid($this->getSessionData('uuid'))) {
            return false;
        }

        return true;
    }

    protected function createSessionIfNew()
    {
        if (! $known = $this->sessionIsKnown()) {
            $model = $this->firstOrCreate(['uuid' => $this->sessionInfo['uuid']]);
            $model->update($this->sessionInfo);

            $this->sessionSetId($model->id);
        } else {
            DB::table('tracker_sessions')
                ->where('id', $this->getSessionData('id'))
                ->limit(1)
                ->update([
                    'updated_at' => strval(Carbon::now())
                ]);/*
            $session = $this->find($this->getSessionData('id'));

            $session->updated_at = Carbon::now();
            $session->save();*/

            $this->sessionInfo['id'] = $this->getSessionData('id');
        }

        return $known;
    }

    protected function ensureSessionDataIsComplete()
    {
        $sessionData = $this->getSessionData();

        $wasComplete = true;

        foreach ($this->sessionInfo as $key => $value) {
            if (! isset($sessionData[$key]) || $sessionData[$key] !== $value) {
                if (! isset($model)) {
                    $model = $this->find($this->sessionInfo['id']);
                }

                // La session de tracking peut ne plus exister en base
                // (ex: base réinitialisée) : on la recrée pour éviter le crash.
                if (! $model) {
                    $model = $this->firstOrCreate(['uuid' => $this->sessionInfo['uuid']]);
                    $this->sessionInfo['id'] = $model->id;
                }

                $model->{$key} = $value;
                $model->save();

                $wasComplete = false;
            }
        }

        if (! $wasComplete) {
            $this->storeSession();
        }
    }

    protected function storeSession()
    {
        $this->putSessionData($this->sessionInfo);
    }

    protected function putSessionData($data)
    {
        $this->session->put($this->sessionKey, $data);
    }

    protected function sessionGetId()
    {
        return $this->sessionInfo['id'];
    }

    protected function sessionSetId($id)
    {
        $this->sessionInfo['id'] = $id;

        $this->storeSession();
    }

    protected function getSystemSessionId()
    {
        $sessionData = $this->getSessionData();

        return data_get($sessionData, 'uuid', (string) UUID::uuid4());
    }

    protected function regenerateSystemSession($data = null)
    {
        $data = $data ?: $this->getSessionData();

        if (! $data) {
            $this->resetSessionUuid($data);

            $this->createSessionIfNew();

            $this->ensureSessionDataIsComplete();
        }

        return $this->sessionInfo;
    }
}