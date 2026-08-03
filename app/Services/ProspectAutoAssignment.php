<?php

namespace App\Services;

use App\Events\ProspectUserAttached;
use App\Models\Import;
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

        $assigned = 0;

        $query->orderBy('id')->chunkById(500, function ($prospects) use ($project, $importId, &$assigned) {
        foreach ($prospects->groupBy('project_id') as $projectId => $projectProspects) {
            $projectModel = ($project && (int)$project->id === (int)$projectId)
                ? $project
                : Project::find($projectId);

            if (!$projectModel) {
                continue;
            }

            // ANCIEN:
            // $assigned += $this->assignBatch($projectModel, $projectProspects);

            // NOUVEAU: on regroupe en plus par import_id, car chaque import
            // a maintenant sa propre liste de rôles éligibles (choisie par
            // l'admin dans l'étape "Relations" du process d'import), au lieu
            // d'une liste de rôles unique codée en dur pour tout le monde.
            foreach ($projectProspects->groupBy('import_id') as $groupImportId => $importProspects) {
                $roleIds = $this->getImportRoleIds($groupImportId ?: $importId);
                $userIds = $this->getImportUserIds($groupImportId ?: $importId);
                $assigned += $this->assignBatch($projectModel, $importProspects, $roleIds, $userIds);
            }
        }
        });

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
     *
     * MODIFIÉ: accepte maintenant $roleIds (rôles sélectionnés pour
     * l'import de ces prospects) et le transmet à getProjectUsers().
     */
    // ANCIEN:
    // protected function assignBatch(Project $project, $prospects): int
    // {
    //     $users = $this->getProjectUsers($project);
    protected function assignBatch(Project $project, $prospects, array $roleIds = [], array $userIds = []): int
    {
        $users = $this->getProjectUsers($project, [], $roleIds, $userIds);
        if ($users->isEmpty()) {
            return 0;
        }

        $counts = $this->getProjectUserCounts($project);
        $orderedUsers = $users->sortBy('id')->values();
        $loads = $orderedUsers->mapWithKeys(fn(User $user) => [$user->id => (int)($counts[$user->id] ?? 0)])->all();

        $assigned = 0;

        foreach ($prospects as $prospect) {
            if ($prospect->users()->exists()) {
                continue;
            }

            $candidate = $this->leastLoadedUser($orderedUsers, $loads);
            if (!$candidate) {
                continue;
            }

            if (!$this->attachProspectToUser($prospect, $candidate)) {
                continue;
            }
            $loads[$candidate->id]++;
            $assigned++;
        }

        return $assigned;
    }

    /**
     * MODIFIÉ: récupère les rôles éligibles depuis l'import du prospect
     * (via getImportRoleIds) au lieu d'une liste codée en dur.
     */
    public function assignProspect(Prospect $prospect): bool
    {
        if ($prospect->users()->exists()) {
            return false;
        }

        $project = $prospect->project;
        if (!$project) {
            return false;
        }

        // ANCIEN:
        // $users = $this->getProjectUsers($project);

        // NOUVEAU: récupère les rôles éligibles configurés sur l'import
        // du prospect, au lieu de la liste codée en dur.
        $roleIds = $this->getImportRoleIds($prospect->import_id);
        $userIds = $this->getImportUserIds($prospect->import_id);

        $users = $this->getProjectUsers($project, [], $roleIds, $userIds);
        if ($users->isEmpty()) {
            return false;
        }

        $counts = $this->getProjectUserCounts($project);
        $orderedUsers = $users->sortBy('id')->values();
        $loads = $orderedUsers->mapWithKeys(fn(User $user) => [$user->id => (int)($counts[$user->id] ?? 0)])->all();

        $candidate = $this->leastLoadedUser($orderedUsers, $loads);
        if (!$candidate) {
            return false;
        }

        return $this->attachProspectToUser($prospect, $candidate);
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

    protected function attachProspectToUser(Prospect $prospect, User $candidate): bool
    {
        $now = Carbon::now();
        $creatorId = auth()->id() ?: $prospect->creator_id ?: null;

        try {
            $prospect->users()->syncWithoutDetaching([
                $candidate->id => [
                    'creator_id' => $creatorId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        } catch (\Illuminate\Database\QueryException $exception) {
            // The prospect (or the user) may have been deleted concurrently
            // (e.g. by a manual cleanup) between the moment it was selected
            // for assignment and this attach. Skip it instead of aborting
            // the whole batch — this would otherwise crash the import job
            // that triggered this assignment.
            Log::warning('ProspectAutoAssignment: skipping assignment, prospect or user no longer exists', [
                'prospect_id' => $prospect->id,
                'candidate_id' => $candidate->id,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }

        Log::info('ProspectAutoAssignment: prospect assigned', [
            'prospect_id' => $prospect->id,
            'candidate_id' => $candidate->id,
            'creator_id' => $creatorId,
            'project_id' => $prospect->project_id,
            'import_id' => $prospect->import_id,
        ]);

        ProspectUserAttached::dispatch($prospect, $candidate);

        return true;
    }

    /**
     * Eligible users for a project: must have one of the given role IDs
     * (selected by the admin for the relevant import), not be banned,
     * not be busy in an ongoing event, and have been active today.
     *
     * MODIFIÉ (ancien code ci-dessous conservé en commentaire):
     * - Le tableau $allowedRoles codé en dur et le filtre sur $user->role
     *   (texte) ne sont plus utilisés, mais laissés en commentaire pour
     *   référence.
     * - AJOUTÉ: le paramètre $roleIds — si aucun rôle n'est fourni (import
     *   sans rôle sélectionné), personne n'est éligible (retourne une
     *   collection vide), plutôt que de deviner une valeur par défaut.
     * - Le filtre par rôle se fait maintenant sur roles.id (IDs choisis par
     *   l'admin) au lieu de roles.name (texte codé en dur).
     * - CONSERVÉ (déjà en place précédemment): le filtre last_activity
     *   (l'utilisateur doit avoir été actif aujourd'hui).
     *
     * @param int[] $roleIds Role IDs allowed to receive prospects automatically.
     */
    // ANCIEN:
    // protected function getProjectUsers(Project $project, array $excludeUserIds = [])
    // {
    //     $allowedRoles = ['agent commercial', 'assistant commercial'];
    //     $allowedRoleNames = array_map('strtolower', $allowedRoles);
    //
    //     $busyUserIds = $this->getUsersInOngoingEvent();
    //
    //     $roleTable = config('permission.table_names.roles', 'roles');
    //     $modelHasRolesTable = config('permission.table_names.model_has_roles', 'model_has_roles');
    //
    //     $roleUserIds = DB::table($modelHasRolesTable)
    //         ->join($roleTable, $roleTable . '.id', '=', $modelHasRolesTable . '.role_id')
    //         ->where($modelHasRolesTable . '.model_type', User::class)
    //         ->where($modelHasRolesTable . '.project_id', $project->id)
    //         ->whereIn(DB::raw('LOWER(' . $roleTable . '.name)'), $allowedRoleNames)
    //         ->pluck($modelHasRolesTable . '.model_id')
    //         ->toArray();
    //
    //     return $project->users()
    //         ->whereNull('banned_at')
    //         ->get(['id', 'name', 'role'])
    //         ->filter(function (User $user) use ($allowedRoleNames, $roleUserIds, $busyUserIds, $excludeUserIds) {
    //             $roleField = trim(strtolower((string) $user->role));
    //
    //             return (
    //                     in_array($roleField, $allowedRoleNames, true)
    //                     || in_array($user->id, $roleUserIds, true)
    //                 )
    //                 && !in_array($user->id, $busyUserIds, true)
    //                 && !in_array($user->id, $excludeUserIds, true);
    //         })
    //         ->values();
    // }
    protected function getProjectUsers(Project $project, array $excludeUserIds = [], array $roleIds = [], array $userIds = [])
    {
        // Neither a role nor an explicit user was selected for this import.
        if (empty($roleIds) && empty($userIds)) {
            return collect();
        }

        $busyUserIds = $this->getUsersInOngoingEvent();

        $roleTable = config('permission.table_names.roles', 'roles');
        $modelHasRolesTable = config('permission.table_names.model_has_roles', 'model_has_roles');

        $roleUserIds = empty($roleIds) ? [] : DB::table($modelHasRolesTable)
            ->join($roleTable, $roleTable . '.id', '=', $modelHasRolesTable . '.role_id')
            ->where($modelHasRolesTable . '.model_type', User::class)
            ->where($modelHasRolesTable . '.project_id', $project->id)
            ->whereIn($roleTable . '.id', $roleIds)
            ->pluck($modelHasRolesTable . '.model_id')
            ->toArray();

        // A person selected directly and through a role must occur only once
        // in the allocation pool. One prospect is therefore assigned once.
        $candidateUserIds = array_values(array_unique(array_merge($roleUserIds, $userIds)));
        if (empty($candidateUserIds)) {
            return collect();
        }

        // Un utilisateur doit avoir eu au moins une activité aujourd'hui
        // (peu importe l'heure exacte) pour être éligible.
        $today = Carbon::today();

        return $project->users()
            ->whereNull('banned_at')
            ->whereIn('users.id', $candidateUserIds)
            ->get(['users.id', 'users.name', 'users.role', 'users.last_activity'])
            ->filter(function (User $user) use ($busyUserIds, $excludeUserIds, $today) {
                return !in_array($user->id, $busyUserIds, true)
                    && !in_array($user->id, $excludeUserIds, true)
                    && $user->last_activity
                    && Carbon::parse($user->last_activity)->isSameDay($today);
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

    /**
     * MODIFIÉ: récupère les rôles éligibles depuis l'import du prospect
     * (getImportRoleIds) au lieu de la liste codée en dur.
     */
    public function reassignUnavailableProspects(?Project $project = null): int
    {
        $unavailableUserIds = $this->getUsersInOngoingEvent();
        if (empty($unavailableUserIds)) {
            return 0;
        }

        $prospectIds = DB::table('prospect_user')
            ->join('prospects', 'prospect_user.prospect_id', '=', 'prospects.id')
            ->whereIn('prospect_user.user_id', $unavailableUserIds)
            ->when($project, fn($q) => $q->where('prospects.project_id', $project->id))
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
            $availableAssigned = array_filter($assignedUserIds, fn($userId) => !in_array($userId, $unavailableUserIds, true));
            if (!empty($availableAssigned)) {
                continue;
            }

            // ANCIEN:
            // $availableUsers = $this->getProjectUsers($project, $assignedUserIds);

            // NOUVEAU: récupère les rôles éligibles configurés sur l'import
            // du prospect, au lieu de la liste codée en dur.
            $roleIds = $this->getImportRoleIds($prospect->import_id);
            $userIds = $this->getImportUserIds($prospect->import_id);
            $availableUsers = $this->getProjectUsers($project, $assignedUserIds, $roleIds, $userIds);
            if ($availableUsers->isEmpty()) {
                continue;
            }

            $counts = $this->getProjectUserCounts($project);
            $orderedUsers = $availableUsers->sortBy('id')->values();
            $loads = $orderedUsers->mapWithKeys(fn(User $user) => [$user->id => (int)($counts[$user->id] ?? 0)])->all();

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

    /**
     * NOUVEAU: rôles sélectionnés par l'admin pour un import donné (étape
     * "Relations" du process d'import). Retourne un tableau vide si
     * l'import n'a aucun rôle configuré, ou si aucun import_id n'est fourni
     * (prospect créé manuellement, hors import).
     */
    protected function getImportRoleIds(?int $importId): array
    {
        if (!$importId) {
            return [];
        }

        $import = Import::find($importId);

        return $import && is_array($import->roles) ? $import->roles : [];
    }

    /**
     * Explicit users selected in the import's Relations step. They are part of
     * the same allocation pool as users supplied by the selected roles.
     */
    protected function getImportUserIds(?int $importId): array
    {
        if (!$importId) {
            return [];
        }

        $import = Import::find($importId);
        if (!$import || !is_array($import->users)) {
            return [];
        }

        return array_values(array_unique(array_filter(
            array_map('intval', $import->users),
            fn ($userId) => $userId > 0
        )));
    }
}
