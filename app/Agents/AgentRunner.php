<?php

namespace App\Agents;

use App\Agents\Claude\ClaudeException;
use App\Agents\Claude\ClaudeGateway;
use App\Agents\Claude\ClaudeGatewayFactory;
use App\Agents\Claude\ClaudeResponse;
use App\Agents\Tools\ToolRegistry;
use App\Models\Agent;
use App\Models\AgentConversation;
use App\Models\AgentMessage;
use App\Models\AgentRun;
use App\Models\AgentRunStep;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Runs one turn of an agent: asks the model, runs the tools it calls, and goes
 * on until it has answered.
 *
 * Guarantees (same spirit as WorkflowEngine)
 *  - One runner per run: the run is claimed with a single atomic UPDATE
 *    (pending -> running). Whoever loses the race does nothing.
 *  - Every answer of the model and every tool result is stored as soon as it
 *    exists, so a run stopped by a crash resumes where it was (see
 *    agents:resume) and a write is never executed twice (see ToolExecutor).
 *  - The history is append only and replayed as stored.
 *  - No transaction is open during a call to the API or to a tool.
 *  - The loop is bounded (agents.max_iterations model calls).
 */
class AgentRunner
{
    public function __construct(
        protected ClaudeGatewayFactory $gateways,
        protected ToolRegistry $tools,
        protected SkillRegistry $skills,
        protected PromptBuilder $prompts,
        protected ToolExecutor $executor,
        protected UsageMeter $usage,
    ) {
    }

    public function run(int $runId): void
    {
        $run = AgentRun::find($runId);

        if (!$run || in_array($run->status, AgentRun::FINISHED, true)) {
            return;
        }

        if (!$this->claim($run)) {
            return;
        }

        try {
            $this->advance($run);
        } catch (ClaudeException|AgentAccessException $e) {
            // Messages written to be shown to the user
            $this->finish($run, AgentRun::FAILED, $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            $this->finish($run, AgentRun::FAILED, "Une erreur interne a interrompu l'agent.");
        }
    }

    /**
     * Fail a run from outside the runner (job given up)
     */
    public function abort(int $runId, string $reason): void
    {
        $run = AgentRun::find($runId);

        if ($run && !in_array($run->status, AgentRun::FINISHED, true)) {
            $this->finish($run, AgentRun::FAILED, $reason);
        }
    }

    /**
     * pending -> running, atomically.
     */
    protected function claim(AgentRun $run): bool
    {
        $claimed = AgentRun::query()
            ->whereKey($run->id)
            ->where('status', AgentRun::PENDING)
            ->update([
                'status' => AgentRun::RUNNING,
                'locked_at' => now(),
                'started_at' => $run->started_at ?? now(),
                'updated_at' => now(),
            ]);

        if ($claimed) {
            $run->refresh();
        }

        return (bool) $claimed;
    }

    protected function advance(AgentRun $run): void
    {
        $agent = Agent::withTrashed()->find($run->agent_id);
        $owner = User::find($run->user_id);
        $project = Project::withoutGlobalScopes()->find($run->project_id);
        $conversation = $run->conversation_id ? AgentConversation::find($run->conversation_id) : null;

        if (!$agent || $agent->trashed() || !$owner || !$project || !$conversation) {
            $this->finish($run, AgentRun::FAILED, 'Agent, utilisateur, projet ou conversation introuvable.');

            return;
        }

        // An agent acts with the rights of its owner, nobody else's
        if ((int) $agent->user_id !== (int) $owner->id || (int) $conversation->project_id !== (int) $project->id) {
            $this->finish($run, AgentRun::FAILED, "Cet agent n'appartient pas à l'utilisateur de cette conversation.");

            return;
        }

        if (!$agent->usableIn($project->id)) {
            $this->finish($run, AgentRun::FAILED, "Cet agent est désactivé ou n'est pas disponible dans ce projet.");

            return;
        }

        $gateway = $this->gateways->make($owner);
        $context = new AgentExecutionContext($owner, $project);

        $skillKeys = $agent->skillKeys();
        $grants = $agent->toolGrants();
        $allowed = $this->allowedToolKeys($skillKeys, $grants);

        $request = [
            'model' => $run->model,
            'system' => $this->prompts->system($agent, $skillKeys),
            'tools' => $this->tools->definitions($this->offered($context, $allowed)),
            'max_tokens' => (int) config('agents.max_output_tokens'),
            'effort' => $agent->effort,
        ];

        $max = (int) config('agents.max_iterations');

        while ($run->iterations < $max) {
            $messages = $this->history($conversation);
            $last = end($messages);

            // A previous attempt stopped after the model had answered
            if ($last && $last['role'] === AgentMessage::ASSISTANT) {
                $pending = $this->toolUsesOf($last['content']);

                if (!$pending) {
                    $this->finish($run, AgentRun::COMPLETED);

                    return;
                }

                $this->answerToolCalls($run, $agent, $conversation, $context, $pending, $allowed, $grants);

                continue;
            }

            $response = $this->ask($run, $gateway, $request + ['messages' => $messages]);

            if ($response->content) {
                $this->store($conversation, $run, AgentMessage::ASSISTANT, $response->content);
            }

            switch ($response->stopReason) {
                case 'tool_use':
                    $uses = $response->toolUses();

                    if (!$uses) {
                        $this->finish($run, AgentRun::FAILED, "Réponse inattendue de l'API Claude.");

                        return;
                    }

                    $this->answerToolCalls($run, $agent, $conversation, $context, $uses, $allowed, $grants);
                    break;

                case 'end_turn':
                case 'stop_sequence':
                    $this->finish($run, AgentRun::COMPLETED);

                    return;

                case 'max_tokens':
                    // A cut tool call must still get an answer to keep the history valid
                    if ($uses = $response->toolUses()) {
                        $this->store($conversation, $run, AgentMessage::USER, array_map(fn ($use) => [
                            'type' => 'tool_result',
                            'tool_use_id' => $use['id'],
                            'content' => 'Réponse interrompue : limite de longueur atteinte.',
                            'is_error' => true,
                        ], $uses));
                    }

                    $this->finish($run, AgentRun::FAILED, 'La réponse de l\'agent a dépassé la longueur maximale autorisée.');

                    return;

                case 'refusal':
                    $this->finish($run, AgentRun::FAILED, "L'API Claude a refusé de répondre à cette demande" . ($response->refusalCategory ? " (catégorie : {$response->refusalCategory})." : '.'));

                    return;

                default:
                    $this->finish($run, AgentRun::FAILED, 'Arrêt inattendu de l\'API Claude : ' . ($response->stopReason ?? 'inconnu') . '.');

                    return;
            }
        }

        $this->finish($run, AgentRun::FAILED, "L'agent a atteint la limite de $max étapes sans conclure.");
    }

    /**
     * One call to the model, recorded in the audit log.
     */
    protected function ask(AgentRun $run, ClaudeGateway $gateway, array $request): ClaudeResponse
    {
        $started = microtime(true);
        $step = AgentRunStep::create([
            'run_id' => $run->id,
            'seq' => ((int) $run->steps()->max('seq')) + 1,
            'type' => AgentRunStep::MODEL_CALL,
            'status' => AgentRunStep::RUNNING,
        ]);

        try {
            $response = $gateway->send($request);
        } catch (ClaudeException $e) {
            $step->update(['status' => AgentRunStep::FAILED, 'error' => $e->reason]);

            throw $e;
        }

        $run->increment('iterations');
        $this->usage->record($run, $response);

        $step->update([
            'status' => AgentRunStep::COMPLETED,
            'input_tokens' => $response->inputTokens,
            'output_tokens' => $response->outputTokens,
            'duration_ms' => (int) round((microtime(true) - $started) * 1000),
        ]);

        return $response;
    }

    protected function answerToolCalls(
        AgentRun $run,
        Agent $agent,
        AgentConversation $conversation,
        AgentExecutionContext $context,
        array $uses,
        array $allowed,
        array $grants,
    ): void {
        $results = [];

        foreach ($uses as $use) {
            $results[] = $this->executor->execute($run, $agent, $context, $use, $allowed, $grants);
        }

        // All the results of a turn go back in ONE message
        $this->store($conversation, $run, AgentMessage::USER, $results);
    }

    /**
     * Tools the model is given: those of the skills and the ones granted
     * one by one, minus the ones denied (a deny always wins).
     *
     * @return string[]
     */
    protected function allowedToolKeys(array $skillKeys, array $grants): array
    {
        $keys = array_merge(
            $this->skills->toolKeys($skillKeys),
            array_keys(array_filter($grants, fn ($access) => $access === 'allow'))
        );
        $denied = array_keys(array_filter($grants, fn ($access) => $access === 'deny'));

        return array_values(array_diff(array_unique($keys), $denied));
    }

    /**
     * The tools shown to the model: only the ones its owner may use in this
     * project. A tool the owner has no right for is not even listed (the gate
     * would refuse the call anyway).
     *
     * @param  string[]  $allowed
     * @return string[]
     */
    protected function offered(AgentExecutionContext $context, array $allowed): array
    {
        return $context->run(function () use ($context, $allowed) {
            return array_values(array_filter($allowed, function (string $key) use ($context) {
                $tool = $this->tools->get($key);

                return $tool && ($tool->ability() === null || auth()->user()->can($tool->ability(), $context->project));
            }));
        });
    }

    protected function history(AgentConversation $conversation): array
    {
        return $conversation->messages()
            ->orderBy('id')
            ->get(['role', 'content'])
            ->map(fn (AgentMessage $message) => ['role' => $message->role, 'content' => $message->content])
            ->all();
    }

    /**
     * @return array<int, array{id: string, name: string, input: array}>
     */
    protected function toolUsesOf(array $content): array
    {
        return (new ClaudeResponse('', '', $content, null))->toolUses();
    }

    protected function store(AgentConversation $conversation, AgentRun $run, string $role, array $content): void
    {
        AgentMessage::create([
            'conversation_id' => $conversation->id,
            'run_id' => $run->id,
            'role' => $role,
            'content' => $content,
        ]);

        $conversation->forceFill(['last_message_at' => now()])->save();
    }

    protected function finish(AgentRun $run, string $status, ?string $error = null): void
    {
        $run->forceFill([
            'status' => $status,
            'error' => $error,
            'finished_at' => now(),
            'locked_at' => null,
        ])->save();

        $this->usage->countRun($run);

        if ($run->conversation_id) {
            AgentConversation::whereKey($run->conversation_id)->update([
                'input_tokens' => DB::raw('input_tokens + ' . (int) $run->input_tokens),
                'output_tokens' => DB::raw('output_tokens + ' . (int) $run->output_tokens),
            ]);
        }
    }
}
