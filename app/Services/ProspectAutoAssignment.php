<?php

namespace App\Services;

use App\Events\ProspectUserAttached;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProspectAutoAssignment
{
    public function assignUnassignedProspects(?Project $project = null, ?int $importId = null): int
    {
        $query = Prospect::doesntHave('users');

        if ($project) {
            $query->where('project_id', $project->id);
        }

        if ($importId) {
            $query->where('import_id', $importId);
        }

        $total = $query->count();
        Log::info('ProspectAutoAssignment: starting auto-assignment', [
            'project_id' => $project?->id,
            'import_id' => $importId,
            'unassigned_prospects' => $total,
        ]);

        $prospects = $query->get();
        $assigned = 0;

        foreach ($prospects->groupBy('project_id') as $projectId => $projectProspects) {
            $projectModel = ($project && (int) $project->id === (int) $projectId)
                ? $project
                : Project::find($projectId);

            if (!$projectModel) {
                continue;
            }

            $assigned += $this->assignBatch($projectModel, $projectProspects);
        }

        if ($assigned !== 0 || $total !== 0) {
            Log::info('ProspectAutoAssignment: completed auto-assignment', [
                'project_id' => $project?->id,
                'import_id' => $importId,
                'unassigned_prospects' => $total,
                'assigned' => $assigned,
            ]);
        }

        return $assigned;
    }

    /**
     * Assign a batch of prospects belonging to the same project, dispatching them
     * proportionally (round-robin on the least-loaded eligible user) using a single
     * in-memory load count instead of re-querying the database for each prospect.
     */
    protected function assignBatch(Project $project, $prospects): int
    {
        $users = $this->getProjectUsers($project);
        if ($users->isEmpty()) {
            return 0;
        }

        $counts = $this->getProjectUserCounts($project);
        $orderedUsers = $users->sortBy('id')->values();
        $loads = $orderedUsers->mapWithKeys(fn (User $user) => [$user->id => (int) ($counts[$user->id] ?? 0)])->all();

        $assigned = 0;

        foreach ($prospects as $prospect) {
            if ($prospect->users()->exists()) {
                continue;
            }

            $candidate = $this->leastLoadedUser($orderedUsers, $loads);
            if (!$candidate) {
                continue;
            }

            $this->attachProspectToUser($prospect, $candidate);
            $loads[$candidate->id]++;
            $assigned++;
        }

        return $assigned;
    }

    public function assignProspect(Prospect $prospect): bool
    {
        if ($prospect->users()->exists()) {
            return false;
        }

        $project = $prospect->project;
        if (!$project) {
            return false;
        }

        $users = $this->getProjectUsers($project);
        if ($users->isEmpty()) {
            return false;
        }

        $counts = $this->getProjectUserCounts($project);
        $orderedUsers = $users->sortBy('id')->values();
        $loads = $orderedUsers->mapWithKeys(fn (User $user) => [$user->id => (int) ($counts[$user->id] ?? 0)])->all();

        $candidate = $this->leastLoadedUser($orderedUsers, $loads);
        if (!$candidate) {
            return false;
        }

        $this->attachProspectToUser($prospect, $candidate);

        return true;
    }

    /**
     * Picks the user with the lowest current load. Ties are broken deterministically
     * by user id (orderedUsers is sorted ascending by id), so the same batch always
     * dispatches prospects the same way — no external/AI dependency involved.
     */
    protected function leastLoadedUser($orderedUsers, array $loads): ?User
    {
        $candidate = null;
        $minLoad = null;

        foreach ($orderedUsers as $user) {
            $load = $loads[$user->id] ?? 0;
            if ($minLoad === null || $load < $minLoad) {
                $minLoad = $load;
                $candidate = $user;
            }
        }

        return $candidate;
    }

    protected function attachProspectToUser(Prospect $prospect, User $candidate): void
    {
        $now = Carbon::now();
        $creatorId = auth()->id() ?: $prospect->creator_id ?: null;

        $prospect->users()->syncWithoutDetaching([
            $candidate->id => [
                'creator_id' => $creatorId,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        Log::info('ProspectAutoAssignment: prospect assigned', [
            'prospect_id' => $prospect->id,
            'candidate_id' => $candidate->id,
            'creator_id' => $creatorId,
            'project_id' => $prospect->project_id,
            'import_id' => $prospect->import_id,
        ]);

        ProspectUserAttached::dispatch($prospect, $candidate);
    }

    protected function getProjectUsers(Project $project, array $excludeUserIds = [])
    {
        $allowedRoles = ['agent commercial', 'assistant commercial'];
        $allowedRoleNames = array_map('strtolower', $allowedRoles);

        $busyUserIds = $this->getUsersInOngoingEvent();

        $roleTable = config('permission.table_names.roles', 'roles');
        $modelHasRolesTable = config('permission.table_names.model_has_roles', 'model_has_roles');

        $roleUserIds = DB::table($modelHasRolesTable)
            ->join($roleTable, $roleTable . '.id', '=', $modelHasRolesTable . '.role_id')
            ->where($modelHasRolesTable . '.model_type', User::class)
            ->where($modelHasRolesTable . '.project_id', $project->id)
            ->whereIn(DB::raw('LOWER(' . $roleTable . '.name)'), $allowedRoleNames)
            ->pluck($modelHasRolesTable . '.model_id')
            ->toArray();

        return $project->users()
            ->whereNull('banned_at')
            ->get(['id', 'name', 'role'])
            ->filter(function (User $user) use ($allowedRoleNames, $roleUserIds, $busyUserIds, $excludeUserIds) {
                $roleField = trim(strtolower((string) $user->role));

                return (
                        in_array($roleField, $allowedRoleNames, true)
                        || in_array($user->id, $roleUserIds, true)
                    )
                    && !in_array($user->id, $busyUserIds, true)
                    && !in_array($user->id, $excludeUserIds, true);
            })
            ->values();
    }

    /**
     * IDs of users currently in an RDV (events.started_at <= now <= events.ended_at).
     */
    protected function getUsersInOngoingEvent(): array
    {
        $now = Carbon::now();

        $directUserIds = DB::table('events')
            ->whereNull('deleted_at')
            ->whereNotNull('user_id')
            ->where('started_at', '<=', $now)
            ->where('ended_at', '>=', $now)
            ->pluck('user_id');

        $pivotUserIds = DB::table('user_event')
            ->join('events', 'events.id', '=', 'user_event.event_id')
            ->whereNull('events.deleted_at')
            ->where('events.started_at', '<=', $now)
            ->where('events.ended_at', '>=', $now)
            ->pluck('user_event.user_id');

        return $directUserIds->merge($pivotUserIds)
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    public function reassignUnavailableProspects(?Project $project = null): int
    {
        $unavailableUserIds = $this->getUsersInOngoingEvent();
        if (empty($unavailableUserIds)) {
            return 0;
        }

        $prospectIds = DB::table('prospect_user')
            ->join('prospects', 'prospect_user.prospect_id', '=', 'prospects.id')
            ->whereIn('prospect_user.user_id', $unavailableUserIds)
            ->when($project, fn ($q) => $q->where('prospects.project_id', $project->id))
            ->distinct('prospect_user.prospect_id')
            ->pluck('prospect_user.prospect_id')
            ->toArray();

        $reassigned = 0;

        foreach ($prospectIds as $prospectId) {
            $prospect = Prospect::with('users')->find($prospectId);
            if (!$prospect) {
                continue;
            }

            $project = $prospect->project;
            if (!$project) {
                continue;
            }

            $assignedUserIds = $prospect->users->pluck('id')->toArray();
            $availableAssigned = array_filter($assignedUserIds, fn ($userId) => !in_array($userId, $unavailableUserIds, true));
            if (!empty($availableAssigned)) {
                continue;
            }

            $availableUsers = $this->getProjectUsers($project, $assignedUserIds);
            if ($availableUsers->isEmpty()) {
                continue;
            }

            $counts = $this->getProjectUserCounts($project);
            $orderedUsers = $availableUsers->sortBy('id')->values();
            $loads = $orderedUsers->mapWithKeys(fn (User $user) => [$user->id => (int) ($counts[$user->id] ?? 0)])->all();

            $candidate = $this->leastLoadedUser($orderedUsers, $loads);
            if (!$candidate) {
                continue;
            }

            DB::table('prospect_user')
                ->where('prospect_id', $prospect->id)
                ->whereIn('user_id', $unavailableUserIds)
                ->delete();

            $this->attachProspectToUser($prospect, $candidate);

            Log::info('ProspectAutoAssignment: prospect reassigned from unavailable user', [
                'prospect_id' => $prospect->id,
                'new_user_id' => $candidate->id,
                'project_id' => $project->id,
            ]);

            $reassigned++;
        }

        return $reassigned;
    }

    protected function getProjectUserCounts(Project $project): array
    {
        return DB::table('prospect_user')
            ->join('prospects', 'prospect_user.prospect_id', '=', 'prospects.id')
            ->where('prospects.project_id', $project->id)
            ->select('prospect_user.user_id', DB::raw('count(*) as total'))
            ->groupBy('prospect_user.user_id')
            ->pluck('total', 'prospect_user.user_id')
            ->toArray();
    }
}
