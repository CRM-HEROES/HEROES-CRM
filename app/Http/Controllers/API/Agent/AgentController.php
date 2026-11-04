<?php

namespace App\Http\Controllers\API\Agent;

use App\Agents\SkillRegistry;
use App\Agents\Tools\ToolRegistry;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentVersion;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * The agents of the authenticated user: list, create, configure, delete.
 *
 * An agent always belongs to its creator and is only ever shown to them. What
 * it can do is bounded elsewhere (PermissionGate): it never has more rights
 * than its owner, whatever is configured here.
 */
class AgentController extends Controller
{
    public function __construct(protected SkillRegistry $skills, protected ToolRegistry $tools)
    {
    }

    public function index(Request $request)
    {
        return Agent::query()->where('user_id', $request->user()->id)->orderBy('name')->get()
            ->map(fn (Agent $agent) => $this->present($agent));
    }

    public function show(Request $request, int $agent)
    {
        return $this->present($this->own($request, $agent), true);
    }

    /**
     * What can be chosen when configuring an agent
     */
    public function catalog()
    {
        return [
            'models' => config('agents.models'),
            'default_model' => config('agents.default_model'),
            'efforts' => ['low', 'medium', 'high', 'xhigh', 'max'],
            'autonomies' => [
                Agent::READ_ONLY => 'Lecture seule : ne modifie rien',
                Agent::PROPOSE => 'Propose : chaque modification attend votre validation',
                Agent::AUTO => 'Autonome : agit seul dans la limite de vos droits (envois et suppressions toujours validés)',
            ],
            'skills' => collect($this->skills->all())->map(fn ($skill, $key) => [
                'key' => $key,
                'label' => $skill['label'],
                'tools' => $skill['tools'],
                'writes' => collect($skill['tools'])->contains(fn ($tool) => ($this->tools->get($tool)?->risk() ?? 'read') !== 'read'),
            ])->values(),
            'tools' => collect($this->tools->all())->map(fn ($tool) => ['key' => $tool->key(), 'module' => $tool->module(), 'risk' => $tool->risk()])->values(),
        ];
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $agent = DB::transaction(function () use ($request, $data) {
            // By default an agent knows every function of the CRM; its rights are its owner's
            $settings = ['all_skills' => $data['skills'] === null || $data['all_skills'] === true];

            $agent = Agent::create($data['agent'] + ['user_id' => $request->user()->id, 'status' => Agent::ACTIVE, 'settings' => $settings]);
            $this->sync($agent, $data);
            $this->publish($agent, $request->user()->id);

            return $agent;
        });

        return response($this->present($agent->fresh(), true), 201);
    }

    public function update(Request $request, int $agent)
    {
        $agent = $this->own($request, $agent);
        $data = $this->validated($request, true);

        DB::transaction(function () use ($agent, $data, $request) {
            $agent->update($data['agent']);

            if ($data['all_skills'] !== null || $data['skills'] !== null) {
                $agent->update(['settings' => ['all_skills' => $data['all_skills'] === true] + ($agent->settings ?? [])]);
            }

            $this->sync($agent, $data);
            $this->publish($agent, $request->user()->id);
        });

        return $this->present($agent->fresh(), true);
    }

    /**
     * Soft delete: its conversations and its audit trail stay.
     */
    public function destroy(Request $request, int $agent)
    {
        $agent = $this->own($request, $agent);
        $agent->update(['status' => Agent::DISABLED]);
        $agent->delete();

        return ['message' => 'Agent supprimé.'];
    }

    // Helpers

    protected function own(Request $request, int $id): Agent
    {
        return Agent::query()->where('user_id', $request->user()->id)->findOrFail($id);
    }

    protected function validated(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        $values = $request->validate([
            'name' => "$required|string|max:100",
            'description' => 'nullable|string|max:1000',
            'specialty' => 'nullable|string|max:100',
            'persona' => 'nullable|string|max:2000',
            'instructions' => 'nullable|string|max:20000',
            'objectives' => 'nullable|string|max:2000',
            'model' => ['sometimes', Rule::in(array_keys(config('agents.models')))],
            'effort' => ['sometimes', Rule::in(['low', 'medium', 'high', 'xhigh', 'max'])],
            'autonomy' => ['sometimes', Rule::in(Agent::AUTONOMIES)],
            'status' => ['sometimes', Rule::in([Agent::ACTIVE, Agent::DISABLED])],
            // null: usable in every project of its owner
            'project' => 'nullable|string',
            'all_skills' => 'sometimes|boolean',
            'skills' => 'sometimes|array|max:20',
            'skills.*' => ['string', Rule::in(array_keys($this->skills->all()))],
            'tool_grants' => 'sometimes|array',
            'tool_grants.*' => ['string', Rule::in(['allow', 'deny'])],
        ]);

        $agent = collect($values)->only(['name', 'description', 'specialty', 'persona', 'instructions', 'objectives', 'model', 'effort', 'autonomy', 'status'])->all();

        if (!$partial) {
            $agent += ['model' => config('agents.default_model'), 'effort' => config('agents.default_effort'), 'autonomy' => config('agents.default_autonomy')];
        }

        if (array_key_exists('project', $values)) {
            // Only a project the user belongs to (the scope of Project)
            $project = $values['project'] ? Project::query()->where((new Project())->getRouteKeyName(), $values['project'])->first() : null;
            abort_if($values['project'] && !$project, 404, 'Projet introuvable.');
            $agent['project_id'] = $project?->id;
        }

        foreach (array_keys($values['tool_grants'] ?? []) as $tool) {
            abort_unless($this->tools->get($tool), 422, "Outil inconnu : $tool.");
        }

        return [
            'agent' => $agent,
            'skills' => $values['skills'] ?? null,
            'all_skills' => $values['all_skills'] ?? null,
            'grants' => $values['tool_grants'] ?? null,
        ];
    }

    protected function sync(Agent $agent, array $data): void
    {
        if ($data['skills'] !== null) {
            DB::table('agent_skills')->where('agent_id', $agent->id)->delete();
            DB::table('agent_skills')->insert(array_map(fn ($key) => ['agent_id' => $agent->id, 'skill_key' => $key, 'created_at' => now(), 'updated_at' => now()], array_values(array_unique($data['skills']))));
        }

        if ($data['grants'] !== null) {
            DB::table('agent_tool_grants')->where('agent_id', $agent->id)->delete();
            DB::table('agent_tool_grants')->insert(array_map(fn ($tool, $access) => ['agent_id' => $agent->id, 'tool_key' => $tool, 'access' => $access, 'created_at' => now(), 'updated_at' => now()], array_keys($data['grants']), $data['grants']));
        }
    }

    /**
     * A snapshot of the configuration, which the runs are pinned to
     */
    protected function publish(Agent $agent, int $userId): void
    {
        $agent->refresh();
        $version = AgentVersion::create([
            'agent_id' => $agent->id,
            'version' => ((int) $agent->versions()->max('version')) + 1,
            'config' => $agent->only(['name', 'persona', 'instructions', 'objectives', 'model', 'effort', 'autonomy']) + ['skills' => $agent->skillKeys(), 'tool_grants' => $agent->toolGrants()],
            'creator_id' => $userId,
        ]);
        $agent->update(['active_version_id' => $version->id]);
    }

    protected function present(Agent $agent, bool $full = false): array
    {
        $base = [
            'id' => $agent->id,
            'name' => $agent->name,
            'description' => $agent->description,
            'specialty' => $agent->specialty,
            'avatar_url' => \App\Agents\AgentAvatar::url($agent->avatar),
            'model' => $agent->model,
            'effort' => $agent->effort,
            'autonomy' => $agent->autonomy,
            'status' => $agent->status,
            'project' => $agent->project?->only(['id', 'name', 'slug']),
            'all_skills' => $agent->hasAllSkills(),
            'skills' => $agent->skillKeys(),
        ];

        return $full ? $base + $agent->only(['persona', 'instructions', 'objectives']) + ['tool_grants' => (object) $agent->toolGrants()] : $base;
    }
}
