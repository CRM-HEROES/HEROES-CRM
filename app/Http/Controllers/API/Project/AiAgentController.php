<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\AiAgent;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiAgentController extends Controller
{
    public function index(Project $project)
    {
        $this->authorizeProject($project);

        Log::channel('ai-phone-agent')->info('AI agent list requested.', [
            'project_id' => $project->id,
            'user_id' => auth()->id(),
        ]);

        return $project->aiAgents()->latest('id')->get()->map(function (AiAgent $agent) {
            return $this->publicAgent($agent);
        })->values();
    }

    public function store(Request $request, Project $project)
    {
        $this->authorizeProject($project);

        $data = $request->validate($this->rules());
        $data['creator_id'] = auth()->id();
        $data['project_id'] = $project->id;

        $agent = AiAgent::create($data);

        Log::channel('ai-phone-agent')->info('AI agent created.', [
            'agent_id' => $agent->id,
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'is_active' => $agent->is_active,
        ]);

        return response()->json($this->publicAgent($agent), 201);
    }

    public function show(Project $project, AiAgent $aiAgent)
    {
        $this->authorizeAgent($project, $aiAgent);

        return $this->publicAgent($aiAgent);
    }

    public function update(Request $request, Project $project, AiAgent $aiAgent)
    {
        $this->authorizeAgent($project, $aiAgent);

        $data = $request->validate($this->rules(true));
        $this->mergeSecrets($data, $request, $aiAgent);
        $aiAgent->update($data);

        Log::channel('ai-phone-agent')->info('AI agent updated.', [
            'agent_id' => $aiAgent->id,
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'changed_fields' => array_values(array_diff(array_keys($data), ['kavkom_config'])),
            'kavkom_config_updated' => array_key_exists('kavkom_config', $data),
        ]);

        return response()->json($this->publicAgent($aiAgent->fresh()));
    }

    public function destroy(Project $project, AiAgent $aiAgent)
    {
        $this->authorizeAgent($project, $aiAgent);
        $aiAgent->delete();

        Log::channel('ai-phone-agent')->info('AI agent deleted.', [
            'agent_id' => $aiAgent->id,
            'project_id' => $project->id,
            'user_id' => auth()->id(),
        ]);

        return ['message' => trans('common.success.deleted_resource')];
    }

    private function rules(bool $update = false): array
    {
        $required = $update ? 'sometimes' : 'required';

        return [
            'name' => [$required, 'string', 'max:150'],
            'is_active' => ['sometimes', 'boolean'],
            'script' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'config' => ['nullable', 'array'],
            'kavkom_config' => ['nullable', 'array'],
            'kavkom_config.api_token' => ['nullable', 'string'],
            'kavkom_config.domain_uuid' => ['nullable', 'string', 'max:100'],
            'kavkom_config.extension' => ['nullable', 'string', 'max:50'],
            'kavkom_config.user_context' => ['nullable', 'string', 'max:255'],
            'kavkom_config.transport' => ['nullable', 'in:udp,tcp,tls'],
            'kavkom_config.sip_port' => ['nullable', 'integer', 'between:1,65535'],
            'kavkom_config.password' => ['nullable', 'string'],
            'kavkom_config.refresh_access_token' => ['nullable', 'string'],
            'kavkom_config.refresh_bearer' => ['nullable', 'string'],
        ];
    }

    private function mergeSecrets(array &$data, Request $request, AiAgent $agent): void
    {
        if (!array_key_exists('kavkom_config', $data)) {
            return;
        }

        $current = $agent->kavkom_config ?: [];
        foreach (['api_token', 'password', 'refresh_access_token', 'refresh_bearer'] as $secret) {
            if (($data['kavkom_config'][$secret] ?? null) === '********') {
                $data['kavkom_config'][$secret] = $current[$secret] ?? null;
            }
        }

        $data['kavkom_config'] = array_filter(
            array_merge($current, $data['kavkom_config']),
            static fn ($value) => $value !== null && $value !== ''
        );
    }

    private function publicAgent(AiAgent $agent): array
    {
        $payload = $agent->toArray();
        $payload['kavkom_config'] = $agent->public_kavkom_config;

        return $payload;
    }

    private function authorizeProject(Project $project): void
    {
        abort_unless(auth()->user()->is_super_admin || auth()->user()->can('', $project), 404);
    }

    private function authorizeAgent(Project $project, AiAgent $agent): void
    {
        $this->authorizeProject($project);
        abort_unless((int) $agent->project_id === (int) $project->id, 404);
    }
}
