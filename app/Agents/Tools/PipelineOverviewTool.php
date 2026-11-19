<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Models\Pipeline;
use App\Models\Prospect;
use Illuminate\Support\Facades\DB;

/**
 * The pipelines of the project (the stages are labels) and how many prospects stand at each stage. The count only
 * reaches the prospects the owner of the agent may see, and leaves out the processed and the deleted ones, as the
 * "Pipeline" page does.
 */
class PipelineOverviewTool implements AgentToolInterface
{
    public function key(): string
    {
        return 'pipeline_overview';
    }

    public function description(): string
    {
        return "Pipelines du projet : sans pipeline_id, la liste des pipelines avec leurs étapes (des labels, dans l'ordre) ; avec pipeline_id, le nombre de prospects à chaque étape. "
            . "Les identifiants d'étapes sont des identifiants de labels : ils servent ensuite à crm_search (filtre label) et à prospect_set_labels.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'pipeline_id' => ['type' => 'integer', 'description' => 'Donne les effectifs par étape de ce pipeline.'],
            ],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'pipeline';
    }

    public function risk(): string
    {
        return self::READ;
    }

    public function ability(): ?string
    {
        return null;
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        $projectId = $context->project->id;

        if (empty($input['pipeline_id'])) {
            $pipelines = Pipeline::query()->where('project_id', $projectId)->orderBy('name')->with('labels')->get();

            return ToolResult::ok(
                ['pipelines' => $pipelines->map(fn (Pipeline $pipeline) => $this->describe($pipeline))->all()],
                'Pipelines du projet',
                $pipelines->count()
            );
        }

        $pipeline = Pipeline::query()->where('project_id', $projectId)->with('labels')->find((int) $input['pipeline_id']);

        if (!$pipeline) {
            return ToolResult::error("Pipeline {$input['pipeline_id']} introuvable dans ce projet.", ToolResult::NOT_FOUND);
        }

        $counts = DB::table('prospect_label')
            ->whereIn('label_id', $pipeline->labels->pluck('id'))
            // The prospects the user may see, in this project, not processed and not deleted
            ->whereIn('prospect_id', Prospect::query()->where('prospects.project_id', $projectId)->whereNull('prospects.processed_at')->select('prospects.id'))
            ->groupBy('label_id')
            ->select('label_id', DB::raw('count(*) as prospects_count'))
            ->pluck('prospects_count', 'label_id');

        $stages = $pipeline->labels->sortBy('pivot.order')->values()->map(fn ($label) => [
            'label_id' => $label->id,
            'name' => mb_substr(strip_tags((string) $label->name), 0, 120),
            'prospects' => (int) ($counts[$label->id] ?? 0),
        ]);

        return ToolResult::ok(
            ['pipeline' => ['id' => $pipeline->id, 'name' => mb_substr(strip_tags((string) $pipeline->name), 0, 120)], 'stages' => $stages->all(), 'total' => $stages->sum('prospects')],
            "Effectifs du pipeline {$pipeline->id}",
            $stages->count()
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }

    protected function describe(Pipeline $pipeline): array
    {
        return [
            'id' => $pipeline->id,
            'name' => mb_substr(strip_tags((string) $pipeline->name), 0, 120),
            'description' => $pipeline->description ? mb_substr(strip_tags((string) $pipeline->description), 0, 200) : null,
            'stages' => $pipeline->labels->sortBy('pivot.order')->values()->map(fn ($label) => ['label_id' => $label->id, 'name' => mb_substr(strip_tags((string) $label->name), 0, 120)])->all(),
        ];
    }
}
