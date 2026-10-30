<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use App\Workflows\Providers\ProviderRegistry;
use App\Workflows\WorkflowCatalog;
use App\Workflows\WorkflowExecutionManager;
use App\Workflows\WorkflowGraphSanitizer;
use App\Workflows\WorkflowService;
use App\Workflows\WorkflowValidationException;
use App\Workflows\WorkflowValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Workflows (automations) of a project.
 *
 * Permissions (checked on the project, like the campaigns; "all.project.workflow.*"
 * with the usual fallback to the parent permissions):
 *   projectWorkflowView     see the workflows and their catalog
 *   projectWorkflowAdd      create a workflow
 *   projectWorkflowUpdate   edit the draft, publish a version
 *   projectWorkflowDelete   delete a workflow
 *   projectWorkflowExecute  activate / pause / archive (what makes it run)
 *
 * Every workflow is looked up in the project of the URL: a workflow of
 * another project is a 404, whatever its id.
 */
class WorkflowController extends Controller
{
    /**
     * Workflows of the project, with how many executions are in each state
     */
    public function index(Project $project)
    {
        $this->authorizeAction($project, 'View');

        $workflows = Workflow::query()
            ->where('project_id', $project->id)
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'status', 'active_version_id', 'legacy_campaign_id', 'created_at', 'updated_at']);

        // One grouped query for the whole list (no query per workflow)
        $executions = WorkflowExecution::query()
            ->whereIn('workflow_id', $workflows->pluck('id'))
            ->selectRaw('workflow_id, status, count(*) as total')
            ->groupBy('workflow_id', 'status')
            ->get()
            ->groupBy('workflow_id');

        return $workflows->map(fn (Workflow $workflow) => $workflow->toArray() + [
            'executions' => ($executions[$workflow->id] ?? collect())->pluck('total', 'status'),
        ])->values();
    }

    /**
     * What the builder offers: nodes, triggers, actions, conditions, variables
     */
    public function catalog(Project $project, WorkflowCatalog $catalog)
    {
        $this->authorizeAction($project, 'View');

        return $catalog->build();
    }

    /**
     * Operators of the project that have a capability (email, sms, call).
     * Nothing but their reference and name: never a credential.
     */
    public function providers(Request $request, Project $project, ProviderRegistry $providers)
    {
        $this->authorizeAction($project, 'View');

        $capability = $request->query('capability');

        abort_unless($capability === null || in_array($capability, ProviderRegistry::CAPABILITIES, true), 422);

        return array_map(
            [ProviderRegistry::class, 'public'],
            $providers->forProject($project->id, $capability)
        );
    }

    public function store(Request $request, Project $project)
    {
        $this->authorizeAction($project, 'Add');

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:2000',
        ]);

        $workflow = Workflow::create($data + [
            'project_id' => $project->id,
            'status' => Workflow::DRAFT,
            'draft_graph' => WorkflowGraphSanitizer::blank(),
            'creator_id' => auth()->id(),
        ]);

        return $this->payload($workflow);
    }

    public function show(Project $project, Workflow $workflow)
    {
        $this->authorizeAction($project, 'View', $workflow);

        return $this->payload($workflow);
    }

    /**
     * Name, description and the draft the builder is editing. The draft is
     * saved as it is, even incomplete; the response says what is still wrong.
     */
    public function update(Request $request, Project $project, Workflow $workflow, WorkflowValidator $validator)
    {
        $this->authorizeAction($project, 'Update', $workflow);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string|max:2000',
            'draft_graph' => 'sometimes|array',
            'draft_graph.nodes' => 'array|max:' . WorkflowGraphSanitizer::MAX_NODES,
            'draft_graph.edges' => 'array|max:' . WorkflowGraphSanitizer::MAX_EDGES,
        ]);

        if (isset($data['draft_graph'])) {
            $data['draft_graph'] = WorkflowGraphSanitizer::clean($data['draft_graph']);
        }

        $workflow->update($data);

        $problems = $validator->diagnose($workflow->draft_graph ?? [], $project->id);

        return $this->payload($workflow) + [
            'errors' => array_column($problems, 'message'),
            'problems' => $problems,
        ];
    }

    public function destroy(Project $project, Workflow $workflow, WorkflowExecutionManager $executions)
    {
        $this->authorizeAction($project, 'Delete', $workflow);

        // Nothing must go on running for a workflow that does not exist anymore
        $executions->cancelAll($workflow->id, 'The workflow was deleted.');
        $workflow->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }

    /**
     * Problems of the draft (or of the graph sent), without saving anything
     */
    public function validateGraph(Request $request, Project $project, Workflow $workflow, WorkflowValidator $validator)
    {
        $this->authorizeAction($project, 'View', $workflow);

        $graph = $request->has('graph')
            ? WorkflowGraphSanitizer::clean((array) $request->input('graph'))
            : ($workflow->draft_graph ?? []);

        $problems = $validator->diagnose($graph, $project->id);

        // "problems" tells which step each one is about
        return ['errors' => array_column($problems, 'message'), 'problems' => $problems];
    }

    /**
     * Publish the draft as the next version. With "activate": true the workflow
     * is also activated (it needs the permission to do so).
     */
    public function publish(Request $request, Project $project, Workflow $workflow, WorkflowService $service)
    {
        $this->authorizeAction($project, 'Update', $workflow);

        $activate = $request->boolean('activate');

        if ($activate) {
            $this->authorizeAction($project, 'Execute', $workflow);
        }

        try {
            $service->publish($workflow, $workflow->draft_graph ?? [], auth()->id());

            if ($activate) {
                $service->activate($workflow->fresh());
            }
        } catch (WorkflowValidationException $e) {
            return $this->invalid($e);
        }

        return $this->payload($workflow->fresh());
    }

    public function activate(Project $project, Workflow $workflow, WorkflowService $service)
    {
        $this->authorizeAction($project, 'Execute', $workflow);

        try {
            $service->activate($workflow);
        } catch (WorkflowValidationException $e) {
            return $this->invalid($e);
        }

        return $this->payload($workflow->fresh());
    }

    public function pause(Project $project, Workflow $workflow, WorkflowService $service)
    {
        $this->authorizeAction($project, 'Execute', $workflow);

        $service->pause($workflow);

        return $this->payload($workflow->fresh());
    }

    public function archive(Project $project, Workflow $workflow, WorkflowService $service)
    {
        $this->authorizeAction($project, 'Execute', $workflow);

        $service->archive($workflow);

        return $this->payload($workflow->fresh());
    }

    /**
     * The workflow and the graph to edit: the draft, or else the published
     * version (a workflow published before drafts, or a new one).
     */
    protected function payload(Workflow $workflow): array
    {
        $version = $workflow->activeVersion;
        $graph = $workflow->draft_graph ?? $version?->graph ?? WorkflowGraphSanitizer::blank();

        return [
            'id' => $workflow->id,
            'name' => $workflow->name,
            'description' => $workflow->description,
            'status' => $workflow->status,
            // The campaign it was converted from, if any
            'legacy_campaign_id' => $workflow->legacy_campaign_id,
            'graph' => $graph,
            'version' => $version ? ['id' => $version->id, 'number' => $version->version, 'published_at' => $version->created_at] : null,
            // The draft differs from what runs
            'unpublished_changes' => $version ? $graph != $version->graph : true,
            'updated_at' => $workflow->updated_at,
        ];
    }

    protected function invalid(WorkflowValidationException $e): JsonResponse
    {
        return response()->json([
            'message' => "Le workflow n'est pas valide.",
            'errors' => $e->errors,
        ], 422);
    }

    /**
     * 404 (like the other project resources) when the user may not, or when the
     * workflow is not in this project
     */
    protected function authorizeAction(Project $project, string $action, ?Workflow $workflow = null): void
    {
        abort_unless(auth()->user()->can("projectWorkflow$action", $project), 404);

        if ($workflow) {
            abort_unless($workflow->project_id == $project->id, 404);
        }
    }
}
