<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\DB;

class CloudTalkCallAttribution
{
    protected Project $project;

    protected ?array $usersByAgent = null;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Only an unambiguous, configured agent belonging to this project may own a call.
     */
    public function userId(?string $agentId): ?int
    {
        if ($agentId === null || trim($agentId) === '') {
            return null;
        }

        if ($this->usersByAgent === null) {
            $lines = $this->project->lines()
                ->where('operator', 'cloudtalk')
                ->whereExists(function ($query) {
                    $query->selectRaw('1')->from('user_project')
                        ->whereColumn('user_project.project_id', 'lines.project_id')
                        ->whereColumn('user_project.user_id', 'lines.user_id');
                })
                ->get(['user_id', 'config']);

            $candidates = [];
            foreach ($lines as $line) {
                $id = $this->normalizeAgentId(data_get($line->config, 'agent_id'));
                if ($id !== null) {
                    $candidates[$id][(int) $line->user_id] = true;
                }
            }

            $this->usersByAgent = [];
            foreach ($candidates as $id => $users) {
                if (count($users) === 1) {
                    $this->usersByAgent[$id] = (int) array_key_first($users);
                }
            }
        }

        return $this->usersByAgent[trim($agentId)] ?? null;
    }

    /**
     * Repair existing calls without replacing a CRM creator or touching call metadata.
     */
    public function backfill(bool $dryRun = false): array
    {
        $result = ['matched' => 0, 'updated' => 0, 'unmatched' => []];

        DB::table('interactions')
            ->where('source', 'cloudtalk')
            ->whereNull('creator_id')
            ->whereIn('prospect_id', $this->project->prospects()->select('prospects.id'))
            ->select('id', 'data')
            ->chunkById(500, function ($calls) use ($dryRun, &$result) {
                $assignments = [];
                foreach ($calls as $call) {
                    $data = json_decode($call->data ?? '{}', true);
                    $agentId = $this->storedAgentId(is_array($data) ? $data : []);
                    $userId = $this->userId($agentId);

                    if ($userId === null) {
                        $key = $agentId ?? 'inconnu';
                        $result['unmatched'][$key] = ($result['unmatched'][$key] ?? 0) + 1;
                        continue;
                    }

                    $result['matched']++;
                    $assignments[$userId][] = $call->id;
                }

                if (!$dryRun) {
                    foreach ($assignments as $userId => $ids) {
                        $result['updated'] += DB::table('interactions')
                            ->whereIn('id', $ids)
                            ->whereNull('creator_id')
                            ->update(['creator_id' => $userId]);
                    }
                }
            });

        return $result;
    }

    protected function storedAgentId(array $data): ?string
    {
        foreach (['cloudtalk_ended.data.user.id', 'cloudtalk_call.agent.id', 'cloudtalk_history.Agent.id'] as $path) {
            $id = $this->normalizeAgentId(data_get($data, $path));
            if ($id !== null) {
                return $id;
            }
        }

        return null;
    }

    protected function normalizeAgentId($value): ?string
    {
        if (!is_string($value) && !is_int($value)) {
            return null;
        }

        $id = trim((string) $value);

        return $id !== '' ? $id : null;
    }
}
