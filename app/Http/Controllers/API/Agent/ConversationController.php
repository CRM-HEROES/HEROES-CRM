<?php

namespace App\Http\Controllers\API\Agent;

use App\Agents\AgentAccessException;
use App\Agents\AgentConversationService;
use App\Agents\AgentRequestException;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentAction;
use App\Models\AgentConversation;
use App\Models\AgentRun;
use App\Models\Project;
use Illuminate\Http\Request;

/**
 * Talking to an agent: send a message, follow the answer, read the history.
 *
 * Everything is bound to the authenticated user: an agent, a conversation or a
 * run of somebody else is a 404. The answer is built by a queued job: ask()
 * returns at once with a run to poll.
 */
class ConversationController extends Controller
{
    public function __construct(protected AgentConversationService $conversations)
    {
    }

    /**
     * The conversations of an agent in a project, most recent first
     */
    public function index(Request $request, int $agent)
    {
        $agent = $this->agent($request, $agent);
        $project = $this->project($request->input('project'));

        return AgentConversation::query()
            ->where('agent_id', $agent->id)->where('user_id', $request->user()->id)->where('project_id', $project->id)
            ->orderByDesc('last_message_at')->limit(50)
            ->get(['id', 'title', 'status', 'last_message_at', 'created_at']);
    }

    /**
     * Sends a message. 202: the agent is answering; poll run().
     */
    public function ask(Request $request, int $agent)
    {
        $data = $request->validate([
            'project' => 'required|string',
            'message' => 'required|string',
            'conversation_id' => 'nullable|integer',
        ]);

        try {
            $run = $this->conversations->ask(
                $this->agent($request, $agent),
                $request->user(),
                $this->project($data['project']),
                $data['message'],
                $data['conversation_id'] ?? null
            );
        } catch (AgentRequestException $e) {
            return response(['message' => $e->getMessage()], 422);
        } catch (AgentAccessException $e) {
            return response(['message' => $e->getMessage()], 403);
        }

        return response(['run_id' => $run->id, 'conversation_id' => $run->conversation_id, 'status' => $run->status], 202);
    }

    /**
     * The history of a conversation, as it is shown: what the user and the agent said,
     * what the agent did. Nothing of the reasoning of the model.
     */
    public function show(Request $request, int $conversation)
    {
        $conversation = $this->conversation($request, $conversation);

        $messages = [];

        foreach ($conversation->messages()->orderBy('id')->get() as $message) {
            foreach ($message->content as $block) {
                $item = $this->block($message->role, $block);

                if ($item) {
                    $messages[] = $item + ['id' => $message->id, 'at' => $message->created_at];
                }
            }
        }

        $last = $conversation->runs()->orderByDesc('id')->first();

        return [
            'id' => $conversation->id,
            'title' => $conversation->title,
            'project_id' => $conversation->project_id,
            'messages' => $messages,
            'run' => $last ? $this->presentRun($last) : null,
            'pending_actions' => AgentAction::query()->where('user_id', $request->user()->id)
                ->whereIn('run_id', $conversation->runs()->select('id'))->where('status', AgentAction::PENDING)
                ->get()->map(fn ($action) => ActionController::present($action))->values(),
        ];
    }

    /**
     * Where a run is: poll it until it is over. Shows what the agent did, step
     * by step (tool, outcome), for the user and for debugging.
     */
    public function run(Request $request, int $run)
    {
        $run = AgentRun::query()->where('user_id', $request->user()->id)->findOrFail($run);

        return $this->presentRun($run, true);
    }

    // Helpers

    protected function agent(Request $request, int $id): Agent
    {
        return Agent::query()->where('user_id', $request->user()->id)->findOrFail($id);
    }

    protected function conversation(Request $request, int $id): AgentConversation
    {
        return AgentConversation::query()->where('user_id', $request->user()->id)->findOrFail($id);
    }

    /**
     * A project the user belongs to (the scope of Project hides the others)
     */
    protected function project(?string $key): Project
    {
        abort_unless($key, 422, 'Le projet est obligatoire.');

        return Project::query()->where((new Project())->getRouteKeyName(), $key)->firstOrFail();
    }

    protected function presentRun(AgentRun $run, bool $withSteps = false): array
    {
        $data = [
            'id' => $run->id,
            'status' => $run->status,
            'finished' => in_array($run->status, AgentRun::FINISHED, true),
            'error' => $run->error,
            'iterations' => $run->iterations,
            'tokens' => ['input' => (int) $run->input_tokens, 'output' => (int) $run->output_tokens],
            'cost' => round($run->cost_micro / 1_000_000, 4),
        ];

        if ($withSteps) {
            $data['steps'] = $run->steps()->where('type', 'tool_call')->orderBy('seq')->get()->map(fn ($step) => [
                'tool' => $step->tool_key,
                'status' => $step->status,
                'decision' => $step->permission_decision,
                'summary' => $step->output_summary,
                'duration_ms' => $step->duration_ms,
            ])->all();
            $data['pending_actions'] = AgentAction::query()->where('run_id', $run->id)->where('status', AgentAction::PENDING)->get()
                ->map(fn ($action) => ActionController::present($action))->values();
        }

        return $data;
    }

    /**
     * One block of a stored message, as a line of the conversation (null: not shown).
     */
    protected function block(string $role, array $block): ?array
    {
        $type = $block['type'] ?? '';

        if ($role === 'assistant') {
            return match ($type) {
                'text' => trim($block['text'] ?? '') === '' ? null : ['role' => 'agent', 'kind' => 'text', 'text' => $block['text']],
                'tool_use' => ['role' => 'agent', 'kind' => 'tool', 'tool' => $block['name'] ?? '?'],
                // The reasoning of the model is never shown
                default => null,
            };
        }

        if ($type !== 'text') {
            // tool results are data for the model, not part of the conversation
            return null;
        }

        $text = (string) ($block['text'] ?? '');

        // The context of the operator is added to each turn for the model
        if (str_starts_with($text, '<contexte_operateur>')) {
            return null;
        }

        if (preg_match('#^<information_systeme>(.*)</information_systeme>$#s', $text, $m)) {
            return ['role' => 'system', 'kind' => 'system', 'text' => $m[1]];
        }

        return ['role' => 'user', 'kind' => 'text', 'text' => $text];
    }
}
