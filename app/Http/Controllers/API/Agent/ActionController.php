<?php

namespace App\Http\Controllers\API\Agent;

use App\Agents\AgentAccessException;
use App\Agents\AgentRequestException;
use App\Agents\ApprovalService;
use App\Http\Controllers\Controller;
use App\Models\AgentAction;
use Illuminate\Http\Request;

/**
 * The actions an agent proposed and that wait for a human, and their outcome.
 *
 * The decision is taken on the server (ApprovalService): this controller only
 * says who asks. Only the user the action belongs to sees it and decides.
 */
class ActionController extends Controller
{
    public function __construct(protected ApprovalService $approvals)
    {
    }

    /**
     * Filters: status (pending by default; "all" for everything), project (id)
     */
    public function index(Request $request)
    {
        $data = $request->validate(['status' => 'nullable|string|max:20', 'project_id' => 'nullable|integer']);
        $status = $data['status'] ?? AgentAction::PENDING;

        $actions = AgentAction::query()
            ->where('user_id', $request->user()->id)
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when(!empty($data['project_id']), fn ($q) => $q->where('project_id', $data['project_id']))
            ->with(['agent:id,name', 'project:id,name,slug'])
            ->orderByDesc('id')->limit(100)->get();

        return $actions->map(fn ($action) => self::present($action))->values();
    }

    public function show(Request $request, int $action)
    {
        return self::present($this->own($request, $action));
    }

    public function approve(Request $request, int $action)
    {
        $action = $this->own($request, $action);

        try {
            $this->approvals->approve($action, $request->user());
        } catch (AgentRequestException $e) {
            return response(['message' => $e->getMessage()], 422);
        } catch (AgentAccessException $e) {
            return response(['message' => $e->getMessage()], 403);
        }

        // With a queue the action may still be running: the status says so
        return response(self::present($action->fresh()), 202);
    }

    public function reject(Request $request, int $action)
    {
        $action = $this->own($request, $action);

        try {
            $this->approvals->reject($action, $request->user());
        } catch (AgentRequestException $e) {
            return response(['message' => $e->getMessage()], 422);
        } catch (AgentAccessException $e) {
            return response(['message' => $e->getMessage()], 403);
        }

        return self::present($action->fresh());
    }

    // Helpers

    protected function own(Request $request, int $id): AgentAction
    {
        return AgentAction::query()->where('user_id', $request->user()->id)->findOrFail($id);
    }

    /**
     * What the screen needs. The payload is not sent back: the action runs from
     * what is stored on the server, and the preview was computed by it.
     */
    public static function present(AgentAction $action): array
    {
        $result = $action->result ?? [];
        $hours = (int) config('agents.action_ttl_hours', 24);

        return [
            'id' => $action->id,
            'agent' => $action->agent?->name,
            'project' => $action->project?->only(['id', 'name', 'slug']),
            'tool' => $action->tool_key,
            'risk' => $action->risk,
            'status' => $action->status,
            'preview' => $action->preview,
            // "running" when it has been approved and the job works through its chunks
            'running' => $action->status === AgentAction::APPROVED,
            'progress' => $result['progress'] ?? null,
            'result' => $action->status === AgentAction::EXECUTED ? array_diff_key($result, ['progress' => 1]) : null,
            'error' => in_array($action->status, [AgentAction::FAILED], true) ? ($result['error'] ?? $action->error) : null,
            'created_at' => $action->created_at,
            'expires_at' => $action->status === AgentAction::PENDING ? $action->created_at->copy()->addHours($hours) : null,
        ];
    }
}
