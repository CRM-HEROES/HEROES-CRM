<?php

namespace App\Agents;

use App\Models\Project;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;

/**
 * The identity an agent acts with: its owner, inside ONE project.
 *
 * The CRM isolates the projects with middlewares and Eloquent scopes that
 * read the authenticated user, the permissions "team" and `request()->project`
 * (see ProjectUserAccessCheck, ProspectScope, Policy, User::is_project_admin).
 * Outside an HTTP request (a queue worker) none of them is set: without this
 * class an agent would see the whole project, whatever the rights of its
 * owner. run() sets the same things the middleware sets, and puts back what
 * was there, since a worker handles many jobs in the same process.
 */
class AgentExecutionContext
{
    public function __construct(
        public readonly User $owner,
        public readonly Project $project,
    ) {
    }

    /**
     * Called by a long action with (done, total), so that its progress can be
     * shown while it runs.
     *
     * @var \Closure|null
     */
    public ?\Closure $progress = null;

    public function reportProgress(int $done, int $total): void
    {
        if ($this->progress) {
            ($this->progress)($done, $total);
        }
    }

    /**
     * Is the owner still allowed in the project? Read at every tool call, so
     * an owner who lost the access stops the agent.
     */
    public function ownerHasAccess(): bool
    {
        if ($this->owner->is_super_admin) {
            return true;
        }

        return DB::table('user_project')
            ->where('project_id', $this->project->id)
            ->where('user_id', $this->owner->id)
            ->exists();
    }

    /**
     * Run the callback as the owner, inside the project.
     *
     * @throws AgentAccessException when the owner has no access to the project
     */
    public function run(Closure $callback): mixed
    {
        if (!$this->ownerHasAccess()) {
            throw new AgentAccessException("Le propriétaire de l'agent n'a pas (ou plus) accès à ce projet.");
        }

        $previous = $this->enter();

        try {
            // Same as ProjectUserAccessCheck
            $this->owner->unsetRelation('roles')->load('roles');

            return $callback($this);
        } finally {
            $this->leave($previous);
        }
    }

    /**
     * Set the owner, the permissions team and the request of the project.
     *
     * @return array the state to give back to leave()
     */
    public function enter(): array
    {
        $previous = [
            'user' => auth()->user(),
            'team' => getPermissionsTeamId(),
            'request' => app('request'),
        ];

        // `request()->project` reads the route parameter "project"
        $request = Request::create('/api/project/' . $this->project->id, 'GET');
        $route = new Route(['GET'], '/api/project/{project}', fn () => null);
        $route->bind($request);
        $route->setParameter('project', $this->project);
        $request->setRouteResolver(fn () => $route);

        app()->instance('request', $request);
        Facade::clearResolvedInstance('request');

        $this->setUserQuietly($this->owner);
        setPermissionsTeamId($this->project->id);

        return $previous;
    }

    /**
     * Put back what enter() replaced.
     */
    public function leave(array $previous): void
    {
        app()->instance('request', $previous['request']);
        Facade::clearResolvedInstance('request');

        if ($previous['user']) {
            $this->setUserQuietly($previous['user']);
        } else {
            auth()->forgetUser();
        }

        setPermissionsTeamId($previous['team']);
    }

    /**
     * Authenticates the user WITHOUT the `Authenticated` event.
     *
     * The CRM listens to it to track the sessions (device, IP, geolocation, see
     * UserAuthenticatedListener): an agent calling a tool is not the user
     * browsing, and must not write into their activity tracking.
     */
    protected function setUserQuietly(Authenticatable $user): void
    {
        $guard = auth()->guard();

        if (!method_exists($guard, 'getDispatcher') || !method_exists($guard, 'setDispatcher')) {
            $guard->setUser($user);

            return;
        }

        $events = $guard->getDispatcher();
        // A dispatcher nobody listens to
        $guard->setDispatcher(new Dispatcher());

        try {
            $guard->setUser($user);
        } finally {
            $guard->setDispatcher($events);
        }
    }
}
