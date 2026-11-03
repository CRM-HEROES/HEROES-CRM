<?php

namespace App\Agents;

use App\Agents\Tools\ToolRegistry;
use App\Agents\Tools\ToolResult;
use App\Jobs\Agent\ExecuteAgentAction;
use App\Models\Agent;
use App\Models\AgentAction;
use App\Models\AgentMessage;
use App\Models\AgentRun;
use App\Models\AgentRunStep;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * The human decision on an action an agent proposed.
 *
 * The confirmation lives on the server: an action only runs when this service
 * has atomically moved it from "pending" to "approved" for the user it belongs
 * to, and the rights are checked again at execution (the owner may have lost
 * them, the tool been withdrawn from the agent, the agent disabled). The
 * action runs from what is stored, not from anything the browser sends.
 */
class ApprovalService
{
    public function __construct(
        protected ToolRegistry $tools,
        protected PermissionGate $gate,
        protected SkillRegistry $skills,
    ) {
    }

    /**
     * @throws AgentRequestException when the action cannot be approved
     */
    public function approve(AgentAction $action, User $approver): AgentAction
    {
        $this->authorize($action, $approver);
        $this->expireIfOld($action);

        // Only one request wins, however many times the button is pressed
        $won = AgentAction::query()
            ->whereKey($action->id)
            ->where('status', AgentAction::PENDING)
            ->update(['status' => AgentAction::APPROVED, 'decided_by' => $approver->id, 'decided_at' => now()]);

        if (!$won) {
            throw new AgentRequestException("Cette action a déjà été traitée ou a expiré.");
        }

        ExecuteAgentAction::dispatch($action->id);

        return $action->fresh();
    }

    /**
     * @throws AgentRequestException
     */
    public function reject(AgentAction $action, User $approver): AgentAction
    {
        $this->authorize($action, $approver);

        $won = AgentAction::query()
            ->whereKey($action->id)
            ->where('status', AgentAction::PENDING)
            ->update(['status' => AgentAction::REJECTED, 'decided_by' => $approver->id, 'decided_at' => now()]);

        if (!$won) {
            throw new AgentRequestException("Cette action a déjà été traitée ou a expiré.");
        }

        $action = $action->fresh();
        $this->record($action, AgentRunStep::DENIED, 'deny', ToolResult::error("L'utilisateur a refusé cette action.", ToolResult::PERMISSION_DENIED));
        $this->tell($action, "L'utilisateur a REFUSÉ l'action #{$action->id} ({$action->tool_key}). Elle n'a pas été exécutée.");

        return $action;
    }

    /**
     * Runs an approved action, as its owner, in its project. Called by the job.
     */
    public function execute(int $actionId): void
    {
        // The claim: a second worker cannot run it again
        $claimed = AgentAction::query()
            ->whereKey($actionId)
            ->where('status', AgentAction::APPROVED)
            ->whereNull('executed_at')
            ->update(['executed_at' => now()]);

        if (!$claimed) {
            return;
        }

        $action = AgentAction::findOrFail($actionId);

        try {
            $result = $this->run($action);
        } catch (AgentAccessException $e) {
            $result = ToolResult::error($e->getMessage(), ToolResult::PERMISSION_DENIED);
        } catch (\Throwable $e) {
            report($e);
            $result = ToolResult::error("L'action a rencontré une erreur interne.", ToolResult::INTERNAL_ERROR);
        }

        $action->update([
            'status' => $result->isError ? AgentAction::FAILED : AgentAction::EXECUTED,
            'result' => $result->isError ? ['error' => $result->data['error'], 'code' => $result->code] : $result->data,
            'error' => $result->isError ? mb_substr($result->summary, 0, 500) : null,
        ]);

        $this->record($action, $result->isError ? AgentRunStep::FAILED : AgentRunStep::COMPLETED, 'approve', $result);
        $this->tell($action, $result->isError
            ? "L'action #{$action->id} ({$action->tool_key}) a ÉCHOUÉ : {$result->data['error']}"
            : "L'action #{$action->id} ({$action->tool_key}) a été EXÉCUTÉE après validation. Résultat : " . json_encode($result->data, JSON_UNESCAPED_UNICODE));
    }

    protected function run(AgentAction $action): ToolResult
    {
        $agent = Agent::query()->find($action->agent_id);
        $owner = User::find($action->user_id);
        $project = Project::withoutGlobalScopes()->find($action->project_id);
        $tool = $this->tools->get($action->tool_key);

        if (!$agent || !$owner || !$project || !$tool) {
            return ToolResult::error("L'agent, l'utilisateur, le projet ou l'outil n'existe plus.", ToolResult::NOT_FOUND);
        }

        if (!$agent->usableIn($project->id)) {
            return ToolResult::error("L'agent est désactivé ou n'est plus disponible dans ce projet.", ToolResult::PERMISSION_DENIED);
        }

        $context = new AgentExecutionContext($owner, $project);

        // The progress of a long action, readable while it runs
        $lastWrite = 0;
        $context->progress = function (int $done, int $total) use ($action, &$lastWrite) {
            if (microtime(true) - $lastWrite >= 1 || $done >= $total) {
                $lastWrite = microtime(true);
                DB::table('agent_actions')->where('id', $action->id)->update(['result' => json_encode(['progress' => ['done' => $done, 'total' => $total]])]);
            }
        };

        return $context->run(function () use ($agent, $context, $tool, $action, $project) {
            // The rights are the ones of NOW
            $ability = $tool->ability() === null ? null : (bool) auth()->user()->can($tool->ability(), $project);
            $skillKeys = $agent->skillKeys();
            $grants = $agent->toolGrants();
            $allowed = array_values(array_diff(
                array_unique(array_merge($this->skills->toolKeys($skillKeys), array_keys(array_filter($grants, fn ($a) => $a === 'allow')))),
                array_keys(array_filter($grants, fn ($a) => $a === 'deny'))
            ));

            // A human is approving: judged as an agent allowed to act
            $decision = $this->gate->decide($tool, Agent::AUTO, $allowed, $grants, $ability);

            if ($decision->isDenied()) {
                return ToolResult::error($decision->reason, ToolResult::PERMISSION_DENIED);
            }

            return $tool->handle($context, $action->payload);
        });
    }

    protected function authorize(AgentAction $action, User $approver): void
    {
        // The action is the owner's: nobody else decides for them
        if ((int) $action->user_id !== (int) $approver->id) {
            throw new AgentAccessException("Cette action ne vous appartient pas.");
        }
    }

    protected function expireIfOld(AgentAction $action): void
    {
        $hours = (int) config('agents.action_ttl_hours', 24);

        if ($action->status === AgentAction::PENDING && $action->created_at->lt(now()->subHours($hours))) {
            $action->update(['status' => AgentAction::EXPIRED]);
        }
    }

    /**
     * An audit row for the decision, next to the row of the proposal.
     */
    protected function record(AgentAction $action, string $status, string $decision, ToolResult $result): void
    {
        $run = AgentRun::find($action->run_id);

        if (!$run) {
            return;
        }

        AgentRunStep::create([
            'run_id' => $run->id,
            'seq' => ((int) $run->steps()->max('seq')) + 1,
            'type' => AgentRunStep::TOOL_CALL,
            'tool_key' => $action->tool_key,
            'tool_use_id' => 'approval:' . $action->tool_use_id,
            'status' => $status,
            'permission_decision' => $decision,
            'input' => ['action_id' => $action->id, 'decided_by' => $action->decided_by],
            'output_summary' => mb_substr($result->summary, 0, 500),
            'rows' => $result->rows,
            'error' => $result->isError ? mb_substr($result->summary, 0, 500) : null,
        ]);
    }

    /**
     * Tells the agent, in the conversation, what the human decided, so the next
     * message of the user continues from the truth. Only when no run is going
     * on in the conversation (a message would otherwise land in the middle of it).
     */
    protected function tell(AgentAction $action, string $text): void
    {
        $run = AgentRun::find($action->run_id);

        if (!$run?->conversation_id) {
            return;
        }

        $busy = AgentRun::query()->where('conversation_id', $run->conversation_id)
            ->whereIn('status', [AgentRun::PENDING, AgentRun::RUNNING])->exists();

        if ($busy) {
            return;
        }

        AgentMessage::create([
            'conversation_id' => $run->conversation_id,
            'role' => 'user',
            'content' => [['type' => 'text', 'text' => "<information_systeme>$text</information_systeme>"]],
        ]);
    }
}
