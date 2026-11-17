<?php

namespace App\Workflows\Actions;

use App\Agents\AgentCommunicationComposer;
use App\Calls\CallEligibility;
use App\Calls\SmsScheduler;
use App\Models\Agent;
use App\Models\AgentDocument;
use App\Models\Project;
use App\Models\WorkflowExecution;
use App\Workflows\Providers\ProviderRegistry;
use App\Workflows\VariableResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;

class AgentSmsAction implements ActionHandlerInterface, ConfigurableAction, ThrottledAction
{
    public function __construct(
        protected ProviderRegistry $providers,
        protected CallEligibility $eligibility,
        protected SmsScheduler $sms,
        protected AgentCommunicationComposer $composer,
    ) {
    }

    public function validateConfig(array $config, ?int $projectId, string $label): array
    {
        $errors = [];
        if (empty($config['agent_id']) || !is_numeric($config['agent_id'])) {
            $errors[] = "L'action \"$label\" (SMS par agent) doit désigner un agent.";
        }
        if (!is_string($config['provider'] ?? null) || $config['provider'] === '') {
            $errors[] = "L'action \"$label\" (SMS par agent) doit désigner un canal SMS.";
        } elseif ($projectId !== null && !$this->providers->find($projectId, $config['provider'], ProviderRegistry::SMS)) {
            $errors[] = "L'action \"$label\" utilise un canal SMS qui n'est plus configuré pour ce projet.";
        }
        if (trim((string) ($config['instruction'] ?? '')) === '') {
            $errors[] = "L'action \"$label\" (SMS par agent) doit contenir une consigne.";
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
        $limit = (int) config('workflows.rate_limits.sms');
        if ($limit <= 0) {
            return null;
        }

        $key = "workflows:rate:{$execution->project_id}:agent_sms:" . ($config['provider'] ?? '');
        return RateLimiter::attempt($key, $limit, fn () => true, 60)
            ? null
            : now()->addSeconds(random_int(15, 75));
    }

    public function handle(Model $subject, array $config, WorkflowExecution $execution): array
    {
        $prospect = $subject instanceof \App\Models\Prospect ? $subject : $subject->prospect;
        if (!$prospect) {
            throw new RuntimeException('Aucun prospect n’est lié à cette action.');
        }

        $project = Project::withoutGlobalScopes()->findOrFail($execution->project_id);
        $agent = Agent::query()->whereKey((int) $config['agent_id'])->first();
        if (!$agent || !$agent->usableIn($project->id) || !$agent->user || !DB::table('user_project')
            ->where('user_id', $agent->user_id)->where('project_id', $project->id)->exists()) {
            throw new RuntimeException('L’agent désigné n’est pas actif ou n’appartient pas à ce projet.');
        }

        $provider = $this->providers->find($project->id, (string) $config['provider'], ProviderRegistry::SMS);
        if (!$provider) {
            throw new RuntimeException('Le canal SMS désigné n’est plus configuré pour ce projet.');
        }
        if (isset($provider['line_id']) && (int) ($provider['user_id'] ?? 0) !== (int) $agent->user_id) {
            throw new RuntimeException('La ligne CloudTalk SMS n’est pas affectée au propriétaire de l’agent.');
        }

        $source = $provider['source'] ?? $provider['operator'];
        $reason = $this->eligibility->checkSms($prospect, $project, null, $provider['user_id'] ?? $agent->user_id, $source);
        if ($reason) {
            throw new RuntimeException(
                'SMS non envoyé [' . $reason . '] : ' . $this->eligibility->smsMessage($reason, $prospect)
            );
        }

        $documentIds = $config['documents'] ?? [];
        if (is_string($documentIds)) {
            $documentIds = preg_split('/[\s,;]+/', $documentIds, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }
        $ids = is_array($documentIds)
            ? array_values(array_unique(array_filter(array_map('intval', $documentIds))))
            : [];
        $documents = AgentDocument::query()->where('agent_id', $agent->id)->whereIn('id', $ids)->get();
        if ($documents->count() !== count($ids)) {
            throw new RuntimeException('Un ou plusieurs documents ne sont pas rattachés à l’agent désigné.');
        }
        $ready = $documents->where('status', 'ready')->map(fn ($document) => [
            'name' => $document->name,
            'summary' => $document->summary,
        ])->values()->all();
        $warnings = $documents->where('status', '!=', 'ready')->map(fn ($document) => $document->name . ' : résumé indisponible, ignoré.')->values()->all();

        $instruction = (new VariableResolver($prospect))->render((string) $config['instruction'], 'text');
        $draft = $this->composer->compose($agent, $prospect, 'sms', $instruction, $ready, $project->name);
        $result = $this->sms->sendOrSchedule(
            $prospect,
            $project,
            $provider['user_id'] ?? $agent->user_id,
            $source,
            $draft['message'],
        );

        return array_filter([
            'provider' => $provider['ref'],
            'sms_id' => $result['sms']->id ?? null,
            'scheduled_at' => $result['scheduled_at'] ?? null,
            'reason' => $result['reason'] ?? null,
            'message' => $result['message'] ?? null,
            'document_warnings' => $warnings ?: null,
        ], fn ($value) => $value !== null);
    }

}
