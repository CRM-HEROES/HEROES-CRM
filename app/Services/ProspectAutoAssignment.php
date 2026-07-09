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
    protected Anthropic $anthropic;

    public function __construct(Anthropic $anthropic)
    {
        $this->anthropic = $anthropic;
    }

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

        foreach ($prospects as $prospect) {
            if ($this->assignProspect($prospect)) {
                $assigned++;
            }
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
        $candidate = $this->chooseUser($prospect, $users, $counts);
        if (!$candidate) {
            return false;
        }

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

        return true;
    }

    protected function getProjectUsers(Project $project)
    {
        $allowedRoles = ['agent commercial', 'assistant commercial'];

        return $project->users()
            ->whereNull('banned_at')
            ->get(['id', 'name', 'role'])
            ->filter(function (User $user) use ($allowedRoles) {
                $role = trim(strtolower((string) $user->role));

                return $user->is_assignable_for_prospect
                    && (
                        $role === ''
                        || in_array($role, $allowedRoles, true)
                    );
            })
            ->values();
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

    protected function chooseUser(Prospect $prospect, $users, array $counts): ?User
    {
        $counts = array_map(fn ($count) => intval($count), $counts);

        $users = $users->map(function (User $user) use ($counts) {
            $user->assigned_count = $counts[$user->id] ?? 0;
            return $user;
        });

        $minCount = min(array_column($users->toArray(), 'assigned_count'));

        $eligible = $users->filter(fn (User $user) => $user->assigned_count === $minCount)->values();
        if ($eligible->isEmpty()) {
            return $users->sortBy('assigned_count')->first();
        }

        $selectedId = $this->requestAssignmentFromAi($prospect, $users, $eligible);
        $selected = $eligible->firstWhere('id', $selectedId);

        if ($selected) {
            Log::info('ProspectAutoAssignment: AI selected a candidate', [
                'prospect_id' => $prospect->id,
                'selected_user_id' => $selected->id,
                'eligible_user_ids' => $eligible->pluck('id')->toArray(),
            ]);
            return $selected;
        }

        return $eligible->first();
    }

    protected function requestAssignmentFromAi(Prospect $prospect, $users, $eligible): ?int
    {
        $message = $this->buildAssignmentPrompt($prospect, $users, $eligible);
        $response = $this->anthropic->sendMessage([
            ['role' => 'user', 'content' => $message],
        ], 512);

        if (!$response) {
            return null;
        }

        return $this->parseUserIdFromAiResponse($response, $eligible->pluck('id')->toArray());
    }

    protected function buildAssignmentPrompt(Prospect $prospect, $users, $eligible): string
    {
        $lines = [
            "Règle d'attribution des prospects :",
            "- Chaque prospect doit obligatoirement être affecté à un utilisateur responsable.",
            "- L'attribution doit être équitable et proportionnelle entre les utilisateurs disponibles.",
            "- Aucun prospect ne doit rester sans utilisateur assigné.",
            "- Parmi les utilisateurs dont la charge est la plus faible, choisis l'utilisateur le plus approprié.",
            "- Réponds uniquement avec un JSON valide contenant {\"user_id\": <id>}.",
            "",
            "Prospect :",
            "- id: {$prospect->id}",
            "- nom: {$prospect->full_name}",
            "- email: {$prospect->email}",
        ];

        $lines[] = "";
        $lines[] = "Utilisateurs disponibles :";

        foreach ($users as $user) {
            $lines[] = sprintf("- id: %d, nom: %s, prospects_assignes: %d", $user->id, $user->name, $user->assigned_count);
        }

        $lines[] = "";
        $lines[] = "Candidats avec la charge la plus faible :";

        foreach ($eligible as $user) {
            $lines[] = sprintf("- id: %d, nom: %s, prospects_assignes: %d", $user->id, $user->name, $user->assigned_count);
        }

        return implode("\n", $lines);
    }

    protected function parseUserIdFromAiResponse(string $response, array $allowedUserIds): ?int
    {
        $response = trim($response);
        $json = null;

        if (preg_match('/\{.*\}/s', $response, $matches)) {
            $json = $matches[0];
        }

        if (!$json) {
            $json = $response;
        }

        $decoded = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        $userId = isset($decoded['user_id']) ? intval($decoded['user_id']) : null;
        if ($userId && in_array($userId, $allowedUserIds, true)) {
            return $userId;
        }

        return null;
    }
}
