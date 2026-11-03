<?php

namespace App\Agents\Writes;

use App\Agents\AgentExecutionContext;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Laravel\Sanctum\Sanctum;

/**
 * Calls a route of the CRM API as the owner of the agent, in process.
 *
 * Writes go through the very routes the interface uses, so validation, global
 * scopes, observers (SMS and e-mail sending, workflow triggers, Google and
 * Pipedrive sync) and project checks are the ones of the CRM: there is no
 * second business logic. The request crosses the whole HTTP kernel, including
 * authentication and the project middlewares.
 *
 * What a route does NOT check (several update / delete / bulk routes check no
 * ability, the interface just hides the button) is checked by the tool, with
 * the ability it declares, before it gets here.
 *
 * Must be called inside AgentExecutionContext::run().
 */
class InternalApi
{
    /**
     * @return InternalResponse
     */
    public function call(AgentExecutionContext $context, string $method, string $path, array $data = []): InternalResponse
    {
        $uri = '/api/project/' . $context->project->getRouteKey() . '/' . ltrim($path, '/');
        $isRead = strtoupper($method) === 'GET';

        $request = Request::create($uri, strtoupper($method), $isRead ? $data : [], [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'application/json',
        ], $isRead ? null : json_encode($data));

        $request->attributes->set('agent_internal', true);

        // What the nested request replaces, and what the caller expects back
        $outerRequest = app('request');
        $defaultGuard = auth()->getDefaultDriver();

        Sanctum::actingAs($context->owner, ['*']);

        try {
            $response = app(Kernel::class)->handle($request);
        } finally {
            app()->instance('request', $outerRequest);
            Facade::clearResolvedInstance('request');
            auth()->shouldUse($defaultGuard);
            // The project middleware set the team: it is the same project
            setPermissionsTeamId($context->project->id);
        }

        $json = json_decode((string) $response->getContent(), true);

        return new InternalResponse($response->getStatusCode(), is_array($json) ? $json : null, (string) $response->getContent());
    }
}
