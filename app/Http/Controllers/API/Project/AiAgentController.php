<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\AiAgent;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        $this->prepareAgentData($data);
        $this->assertUniquePhoneConfig($project, $data);
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
        $this->prepareAgentData($data);
        $this->assertUniquePhoneConfig($project, $data, $aiAgent);
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
        $configFieldRequired = $update ? 'required_with:config' : 'required';
        $kavkomFieldRequired = $update ? 'required_with:kavkom_config' : 'required';
        $liveModels = ['models/gemini-2.5-flash-native-audio-preview-09-2025'];
        $summaryModels = ['models/gemini-3.8-flash'];

        return [
            'name' => [$required, 'string', 'max:150'],
            'is_active' => ['sometimes', 'boolean'],
            'script' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'config' => [$required, 'array'],
            'config.gemini_api_key' => [$configFieldRequired, 'string'],
            'config.gemini_live_model' => [$configFieldRequired, 'string', Rule::in($liveModels)],
            'config.gemini_summary_model' => [$configFieldRequired, 'string', Rule::in($summaryModels)],
            'kavkom_config' => [$required, 'array'],
            'kavkom_config.extension' => [$kavkomFieldRequired, 'string', 'max:50'],
            'kavkom_config.password' => [$kavkomFieldRequired, 'string'],
            'kavkom_config.caller_id_number' => [$kavkomFieldRequired, 'string', 'max:50'],
        ];
    }

    private function mergeSecrets(array &$data, Request $request, AiAgent $agent): void
    {
        if (array_key_exists('config', $data)) {
            $currentConfig = $agent->config ?: [];

            if (($data['config']['gemini_api_key'] ?? null) === '********') {
                $data['config']['gemini_api_key'] = $currentConfig['gemini_api_key'] ?? null;
            }
        }

        if (!array_key_exists('kavkom_config', $data)) {
            return;
        }

        $current = $agent->kavkom_config ?: [];
        if (($data['kavkom_config']['password'] ?? null) === '********') {
            $data['kavkom_config']['password'] = $current['password'] ?? null;
        }
    }

    private function prepareAgentData(array &$data): void
    {
        if (array_key_exists('config', $data)) {
            $data['config'] = array_filter([
                'gemini_api_key' => $data['config']['gemini_api_key'] ?? null,
                'gemini_live_model' => $data['config']['gemini_live_model'] ?? null,
                'gemini_summary_model' => $data['config']['gemini_summary_model'] ?? null,
            ], static fn ($value) => $value !== null && $value !== '');
        }

        if (array_key_exists('kavkom_config', $data)) {
            $data['kavkom_config'] = array_filter([
                'extension' => $data['kavkom_config']['extension'] ?? null,
                'password' => $data['kavkom_config']['password'] ?? null,
                'caller_id_number' => $data['kavkom_config']['caller_id_number'] ?? null,
            ], static fn ($value) => $value !== null && $value !== '');
        }
    }

    private function assertUniquePhoneConfig(Project $project, array $data, ?AiAgent $currentAgent = null): void
    {
        if (!array_key_exists('kavkom_config', $data)) {
            return;
        }

        $extension = strtolower(trim((string) ($data['kavkom_config']['extension'] ?? '')));
        $callerIdNumber = $this->digitsOnly($data['kavkom_config']['caller_id_number'] ?? null);

        if ($extension === '' && $callerIdNumber === '') {
            return;
        }

        $conflictingAgent = AiAgent::query()
            ->where('project_id', $project->id)
            ->when($currentAgent, fn ($query) => $query->where('id', '!=', $currentAgent->id))
            ->get(['id', 'name', 'kavkom_config'])
            ->first(function (AiAgent $agent) use ($extension, $callerIdNumber) {
                $config = $agent->kavkom_config ?: [];
                $sameExtension = $extension !== ''
                    && strtolower(trim((string) ($config['extension'] ?? ''))) === $extension;
                $samePhone = $callerIdNumber !== ''
                    && $this->digitsOnly($config['caller_id_number'] ?? $config['phone_number'] ?? null) === $callerIdNumber;

                return $sameExtension || $samePhone;
            });

        if ($conflictingAgent) {
            throw ValidationException::withMessages([
                'kavkom_config.caller_id_number' => "Ce téléphone ou cette extension est déjà utilisé par l'agent IA \"{$conflictingAgent->name}\".",
            ]);
        }
    }

    private function publicAgent(AiAgent $agent): array
    {
        $payload = $agent->toArray();
        $payload['config'] = $agent->public_config;
        $payload['kavkom_config'] = $agent->public_kavkom_config;

        return $payload;
    }

    private function digitsOnly(?string $number): string
    {
        return preg_replace('/\D+/', '', (string) $number) ?: '';
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
