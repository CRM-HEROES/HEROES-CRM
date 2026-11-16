<?php

namespace App\Workflows\Actions;

use App\Agents\AgentCommunicationComposer;
use App\Calls\CallCompletion;
use App\Calls\CallEligibility;
use App\Calls\Voice\VoiceProvider;
use App\Models\Agent;
use App\Models\AgentDocument;
use App\Models\CallAttempt;
use App\Models\Project;
use App\Models\WorkflowExecution;
use App\Workflows\VariableResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AgentCallAction implements ActionHandlerInterface, ConfigurableAction, ThrottledAction
{
    public function __construct(
        protected CallEligibility $eligibility,
        protected VoiceProvider $provider,
        protected CallCompletion $completion,
        protected AgentCommunicationComposer $composer,
    ) {
    }

    public function validateConfig(array $config, ?int $projectId, string $label): array
    {
        $errors = [];
        if (empty($config['agent_id']) || !is_numeric($config['agent_id'])) {
            $errors[] = "L'action \"$label\" (Appel par agent) doit désigner un agent.";
        }
        if (trim((string) ($config['instruction'] ?? '')) === '') {
            $errors[] = "L'action \"$label\" (Appel par agent) doit contenir une consigne.";
        }
        if (!in_array($config['on_error'] ?? 'fail', ['fail', 'continue'], true)) {
            $errors[] = "L'action \"$label\" a un mode d'erreur invalide.";
        }
        foreach (VariableResolver::unknown((string) ($config['instruction'] ?? '')) as $variable) {
            $errors[] = "L'action \"$label\" utilise une variable inconnue : {{" . $variable . '}}.';
        }

        return $errors;
    }

    public function delay(WorkflowExecution $execution, array $config): ?\Carbon\CarbonInterface
    {
        $limit = (int) config('workflows.rate_limits.call');
        if ($limit <= 0) {
            return null;
        }

        $key = "workflows:rate:{$execution->project_id}:agent_call:" . ($config['agent_id'] ?? '');
        return \Illuminate\Support\Facades\RateLimiter::attempt($key, $limit, fn () => true, 60)
            ? null
            : now()->addSeconds(random_int(15, 75));
    }

    public function handle(Model $subject, array $config, WorkflowExecution $execution): array
    {
        $prospect = $this->prospect($subject);
        $project = Project::withoutGlobalScopes()->findOrFail($execution->project_id);
        $agent = $this->agent($config, $project);
        $attempt = $this->eligibility->prepare($prospect, $agent, $project);
        $reason = $this->eligibility->reason($prospect, $project);

        if ($reason) {
            $attempt->update(['status' => CallAttempt::SKIPPED, 'skip_reason' => $reason, 'scheduled_at' => null]);
            throw new RuntimeException(
                'Appel non lancé [' . $reason . '] : ' . $this->eligibility->callMessage($reason, $prospect)
            );
        }

        try {
            $documents = $this->documentSummaries($agent, $this->documentIds($config['documents'] ?? []));
            $instruction = (new VariableResolver($prospect))->render((string) $config['instruction'], 'text');
            $draft = $this->composer->compose($agent, $prospect, 'call', $instruction, $documents['ready'], $project->name);
            $briefing = array_merge($draft, [
                'instruction' => $instruction,
                'documents' => $documents['ready'],
                'document_warnings' => $documents['warnings'],
            ]);
        } catch (\Throwable $exception) {
            $attempt->update(['status' => CallAttempt::FAILED, 'skip_reason' => $exception->getMessage(), 'ended_at' => now()]);
            throw $exception;
        }

        $attempt->update(['status' => CallAttempt::CALLING, 'started_at' => now()]);
        try {
            $result = $this->provider->start($attempt, $prospect, $briefing);
        } catch (\RuntimeException $exception) {
            $attempt->update(['status' => CallAttempt::FAILED, 'skip_reason' => $exception->getMessage(), 'ended_at' => now()]);
            throw $exception;
        }

        if (($result['outcome'] ?? null) === 'pending') {
            $attempt->update([
                'external_id' => $result['external_id'] ?? null,
                'summary' => json_encode($briefing, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
            ]);

            return ['call_attempt_id' => $attempt->id, 'status' => CallAttempt::CALLING, 'external_id' => $attempt->external_id];
        }

        $this->completion->complete($attempt, $prospect, $project, $agent->user, $result);

        return ['call_attempt_id' => $attempt->id, 'status' => $attempt->fresh()->status, 'outcome' => $result['outcome'] ?? null];
    }

    protected function agent(array $config, Project $project): Agent
    {
        $agent = Agent::query()->whereKey((int) $config['agent_id'])->first();
        if (!$agent || !$agent->usableIn($project->id) || !$agent->user || !DB::table('user_project')
            ->where('user_id', $agent->user_id)->where('project_id', $project->id)->exists()) {
            throw new RuntimeException('L’agent désigné n’est pas actif ou n’appartient pas à ce projet.');
        }

        return $agent;
    }

    protected function documentSummaries(Agent $agent, array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        $documents = AgentDocument::query()->where('agent_id', $agent->id)->whereIn('id', $ids)->get();
        if ($documents->count() !== count($ids)) {
            throw new RuntimeException('Un ou plusieurs documents ne sont pas rattachés à l’agent désigné.');
        }

        return [
            'ready' => $documents->where('status', 'ready')->map(fn ($document) => [
                'name' => $document->name,
                'summary' => $document->summary,
            ])->values()->all(),
            'warnings' => $documents->where('status', '!=', 'ready')->map(fn ($document) => $document->name . ' : résumé indisponible, ignoré.')->values()->all(),
        ];
    }

    protected function documentIds(mixed $value): array
    {
        if (is_string($value)) {
            $value = preg_split('/[\s,;]+/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        return is_array($value) ? $value : [];
    }

    protected function prospect(Model $subject): \App\Models\Prospect
    {
        $prospect = $subject instanceof \App\Models\Prospect ? $subject : $subject->prospect;
        if (!$prospect) {
            throw new RuntimeException('Aucun prospect n’est lié à cette action.');
        }

        return $prospect;
    }

}
