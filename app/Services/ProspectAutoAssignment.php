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
        // ARCHER (P6): within each fetched page, prospects flagged as
        // top-20% ("tête de file") are assigned before the rest, so a
        // high-scoring prospect isn't stuck behind older, lower-priority
        // ones purely because of import order.
        $prospects = $prospects->sortByDesc(function ($prospect) {
            return ($prospect->archer_priority ? 1000 : 0) + ($prospect->archer_score ?? 0);
        })->values();

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
     * one by one in strict round-robin order across the eligible users (1st
     * prospect to the 1st user, 2nd to the 2nd, ..., wrapping back to the 1st
     * once every user has had a turn) — so N prospects split evenly across
     * K users regardless of each user's pre-existing historical load.
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

        $orderedUsers = $users->sortBy('id')->values();
        $loadMap = $this->getUserLoadCounts($project, $orderedUsers->pluck('id')->all());

        $assigned = 0;

        foreach ($prospects as $prospect) {
            if ($prospect->users()->exists()) {
                continue;
            }

            $candidate = $this->pickLeastLoadedUser($orderedUsers, $loadMap);

            if (!$this->assignIfStillUnassigned($prospect, $candidate)) {
                continue;
            }
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

        $orderedUsers = $users->sortBy('id')->values();
        $loadMap = $this->getUserLoadCounts($project, $orderedUsers->pluck('id')->all());
        $candidate = $this->pickLeastLoadedUser($orderedUsers, $loadMap);

        return $this->assignIfStillUnassigned($prospect, $candidate);
    }

    /**
     * The end-of-import batch assignment (assignUnassignedProspects, called
     * from ImportProspects) and the "app:assign-prospects" scheduled command
     * (every 5 minutes, via assignUnassignedProspects too) both scan the
     * same pool of prospects with no users yet. A large import can still be
     * running when the schedule fires, so both processes see the same
     * unassigned prospect at once: each passes the earlier "no user yet"
     * check, then each attaches its own (different) candidate — the
     * prospect ends up with two owners instead of one, breaking the 1-2-3
     * proportional split. Locking the prospect row for the check+attach
     * serializes concurrent callers: whichever commits first wins, the
     * other sees the row already assigned once it acquires the lock.
     */
    protected function assignIfStillUnassigned(Prospect $prospect, User $candidate): bool
    {
        return DB::transaction(function () use ($prospect, $candidate) {
            DB::table('prospects')->where('id', $prospect->id)->lockForUpdate()->first();

            if (DB::table('prospect_user')->where('prospect_id', $prospect->id)->exists()) {
                return false;
            }

            return $this->attachProspectToUser($prospect, $candidate);
        });
    }

    /**
     * MODIFIÉ: remplace l'ancienne rotation round-robin (compteur Redis
     * clé par pool exact d'utilisateurs éligibles) par une sélection basée
     * sur la charge réelle actuelle de chaque utilisateur dans le projet.
     *
     * L'ancien round-robin utilisait une clé Redis dérivée du pool exact
     * de candidats (implode('-', $candidateUserIds)). Deux imports dont les
     * pools se chevauchent partiellement (ex. import A -> rôle "Commercial",
     * import B -> rôles "Commercial"+"Assistant") utilisaient donc deux
     * compteurs indépendants : un utilisateur éligible aux deux pools
     * recevait des leads des deux rotations sans jamais être comparé
     * équitablement à un utilisateur qui n'est éligible qu'à un seul pool -
     * ce qui cassait la répartition globale malgré un round-robin "correct"
     * au sein de chaque pool pris isolément.
     *
     * Se base sur le nombre de prospects réellement assignés à chaque
     * utilisateur (getUserLoadCounts), qui s'auto-corrige quel que soit le
     * chevauchement des pools, et retire la dépendance à Redis (qui doit
     * être démarré pour fonctionner) pour cette sélection.
     *
     * @param \Illuminate\Support\Collection $orderedUsers Eligible users, sorted by id.
     * @param array $loadMap [$userId => currently assigned count], mutated in place.
     */
    protected function pickLeastLoadedUser($orderedUsers, array &$loadMap): User
    {
        $bestUser = null;
        $bestLoad = null;

        foreach ($orderedUsers as $user) {
            $load = $loadMap[$user->id] ?? 0;

            // Users are iterated in ascending id order and only replace the
            // current best on a strictly lower load, so ties are broken by
            // the lowest id.
            if ($bestLoad === null || $load < $bestLoad) {
                $bestUser = $user;
                $bestLoad = $load;
            }
        }

        $loadMap[$bestUser->id] = $bestLoad + 1;

        return $bestUser;
    }

    /**
     * Number of prospects currently assigned to each of the given users
     * within the project, used as the fairness signal by
     * pickLeastLoadedUser(). Users with no prospects yet are included with
     * a count of 0.
     */
    protected function getUserLoadCounts(Project $project, array $userIds): array
    {
        $loadMap = array_fill_keys($userIds, 0);

        if (empty($userIds)) {
            return $loadMap;
        }

        $counts = DB::table('prospect_user')
            ->join('prospects', 'prospects.id', '=', 'prospect_user.prospect_id')
            ->where('prospects.project_id', $project->id)
            ->whereIn('prospect_user.user_id', $userIds)
            ->select('prospect_user.user_id', DB::raw('COUNT(*) as total'))
            ->groupBy('prospect_user.user_id')
            ->pluck('total', 'user_id');

        foreach ($counts as $userId => $total) {
            $loadMap[$userId] = (int) $total;
        }

        return $loadMap;
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

        // SUPPRIMÉ: l'exigence "actif aujourd'hui" (last_activity du jour).
        // Elle excluait totalement les utilisateurs sélectionnés pour un
        // import dès qu'ils n'avaient pas encore ouvert l'appli dans la
        // journée, ramenant le pool de candidats à 0 (ou 1, cassant la
        // répartition proportionnelle) même si l'utilisateur restait un
        // destinataire valide pour ces prospects.
        return $project->users()
            ->whereNull('banned_at')
            ->whereIn('users.id', $candidateUserIds)
            ->get(['users.id', 'users.name', 'users.role', 'users.last_activity'])
            ->filter(function (User $user) use ($busyUserIds, $excludeUserIds) {
                return !in_array($user->id, $busyUserIds, true)
                    && !in_array($user->id, $excludeUserIds, true);
            })
            ->values();
    }

    /**
     * IDs of users currently "off" — not just in any ongoing RDV, but in an
     * ongoing event that is either longer than 1 day (an absence/leave
     * rather than a short appointment) or booked on the "Off" calendar.
     * A short RDV no longer makes a user ineligible for auto-assignment.
     */
    protected function getUsersInOngoingEvent(): array
    {
        $now = Carbon::now();

        $offCondition = function ($query) {
            $query->where('calendars.name', 'Off')
                ->orWhereRaw('TIMESTAMPDIFF(SECOND, events.started_at, events.ended_at) > 86400');
        };

        $directUserIds = DB::table('events')
            ->join('calendars', 'calendars.id', '=', 'events.calendar_id')
            ->whereNull('events.deleted_at')
            ->whereNotNull('events.user_id')
            ->where('events.started_at', '<=', $now)
            ->where('events.ended_at', '>=', $now)
            ->where($offCondition)
            ->pluck('events.user_id');

        $pivotUserIds = DB::table('user_event')
            ->join('events', 'events.id', '=', 'user_event.event_id')
            ->join('calendars', 'calendars.id', '=', 'events.calendar_id')
            ->whereNull('events.deleted_at')
            ->where('events.started_at', '<=', $now)
            ->where('events.ended_at', '>=', $now)
            ->where($offCondition)
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

            $orderedUsers = $availableUsers->sortBy('id')->values();
            $loadMap = $this->getUserLoadCounts($project, $orderedUsers->pluck('id')->all());
            $candidate = $this->pickLeastLoadedUser($orderedUsers, $loadMap);

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
