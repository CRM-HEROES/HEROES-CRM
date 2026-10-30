<?php

namespace App\Workflows\Legacy;

use App\Jobs\Workflow\BackfillLegacyCampaign;
use App\Models\Campaign;
use App\Models\CampaignOperator;
use App\Models\CampaignRule;
use App\Models\Workflow;
use App\Workflows\Actions\ActionRegistry;
use App\Workflows\ConditionEvaluator;
use App\Workflows\WorkflowService;
use App\Workflows\WorkflowValidationException;
use Carbon\Carbon;

/**
 * Converts a legacy campaign (app:campaign) into a workflow.
 *
 * What a campaign is: "on a schedule, for every prospect that matches these
 * rules and was not processed yet, do these actions". What it becomes: a
 * workflow that starts when a prospect ENTERS the filter made of the same
 * rules, once per prospect, and runs the same actions in the same order.
 * Nothing is rewritten: the rules keep their filters, the actions keep their
 * handlers and their values (the campaign actions are reused as they are).
 *
 * The rules of a campaign are an OR of its rules and operators, an operator
 * being an AND / OR group of rules and of other operators, and the "count"
 * rules being ANDed to the whole. The condition tree of the new engine has the
 * same structure, so the same prospects are selected (this is tested).
 *
 * Only what can be converted faithfully is: campaigns on prospects that do not
 * re-process. The others (quotes, appointments, "re-process", one-shot
 * campaigns) are reported with the reason and keep running as they do now.
 *
 * The campaign itself is never modified by the conversion; only the cutover
 * (cutover()) switches it off, once the workflow is active.
 */
class LegacyCampaignConverter
{
    public const CONVERTED = 'converted';
    public const CONVERTIBLE = 'convertible';
    public const MANUAL = 'manual';
    public const FINISHED = 'finished';

    public function __construct(
        protected WorkflowService $service,
        protected ActionRegistry $actions,
    ) {
    }

    /**
     * Can the campaign be converted, and what changes if it is?
     *
     * @return array{status: string, reasons: string[], notes: string[], workflow_id: ?int}
     */
    public function analyze(Campaign $campaign): array
    {
        $existing = $this->workflowOf($campaign);

        if ($existing) {
            return $this->result(self::CONVERTED, ["Déjà convertie (automatisation n°{$existing->id})."], [], $existing->id);
        }

        if ($campaign->for !== 'prospect') {
            $what = $campaign->for === 'order' ? 'les devis' : 'les RDV';

            return $this->result(self::MANUAL, ["Campagne sur $what : la conversion automatique n'existe que pour les prospects. Elle continue de fonctionner comme avant."]);
        }

        if ($campaign->re_process_processed_prospects) {
            return $this->result(self::MANUAL, ['Cette campagne retraite les prospects déjà traités à chaque passage : une automatisation ne le fait pas. Elle continue de fonctionner comme avant.']);
        }

        if ($campaign->frequency === 'once') {
            return $campaign->execution > 0
                ? $this->result(self::FINISHED, ['Exécution unique déjà effectuée : rien à convertir.'])
                : $this->result(self::MANUAL, ['Exécution unique planifiée : une automatisation réagit en continu, pas une seule fois. Elle s\'exécutera comme prévu.']);
        }

        $actions = $campaign->actions()->orderBy('campaign_action.id')->get();

        if ($actions->isEmpty()) {
            return $this->result(self::MANUAL, ['La campagne n\'a aucune action.']);
        }

        $unknown = $actions->filter(fn ($action) => !$this->actions->has($action->action, 'prospect'))->pluck('action');

        if ($unknown->isNotEmpty()) {
            return $this->result(self::MANUAL, ['Action inconnue du nouveau moteur : ' . $unknown->unique()->implode(', ') . '.']);
        }

        $tree = $this->tree($campaign);

        if (!$tree) {
            return $this->result(self::MANUAL, ['La campagne n\'a aucune règle : elle s\'applique à tous les prospects, ce qu\'un filtre à surveiller ne peut pas exprimer.']);
        }

        return $this->result(self::CONVERTIBLE, [], $this->notes($campaign, $tree));
    }

    /**
     * Creates the workflow (a draft, published as version 1) from the campaign.
     * It does nothing else: the campaign keeps running until the cutover.
     *
     * @throws LegacyConversionException
     */
    public function convert(Campaign $campaign, ?int $userId = null): Workflow
    {
        $analysis = $this->analyze($campaign);

        if ($analysis['status'] !== self::CONVERTIBLE) {
            throw new LegacyConversionException($analysis['reasons']);
        }

        $workflow = Workflow::create([
            'project_id' => $campaign->project_id,
            'legacy_campaign_id' => $campaign->id,
            'name' => mb_substr("Campagne : {$campaign->name}", 0, 150),
            'description' => mb_substr(trim("Convertie de la campagne n°{$campaign->id}. " . (string) $campaign->description), 0, 2000),
            'status' => Workflow::DRAFT,
            'draft_graph' => $graph = $this->graph($campaign),
            'creator_id' => $userId ?? $campaign->creator_id,
        ]);

        try {
            $this->service->publish($workflow, $graph, $userId ?? $campaign->creator_id);
        } catch (WorkflowValidationException $e) {
            // Nothing half done: a failed conversion can be tried again
            $workflow->forceDelete();

            throw new LegacyConversionException($e->errors);
        }

        return $workflow->fresh();
    }

    /**
     * Hands the campaign over to its workflow: the workflow is activated, the
     * campaign is switched off (so the scheduler stops running it), and the
     * prospects that already match and that the campaign had not processed
     * yet are given to the workflow (BackfillLegacyCampaign).
     *
     * Idempotent. In this order on purpose: if the activation is refused
     * (invalid workflow), the campaign is left running.
     *
     * @throws WorkflowValidationException
     */
    public function cutover(Campaign $campaign, Workflow $workflow): void
    {
        if ($workflow->status !== Workflow::ACTIVE) {
            $this->service->activate($workflow);
        }

        if ($campaign->active) {
            $campaign->update(['active' => false]);
        }

        BackfillLegacyCampaign::dispatch($workflow->id, $campaign->id);
    }

    public function workflowOf(Campaign $campaign): ?Workflow
    {
        return Workflow::withTrashed()->where('legacy_campaign_id', $campaign->id)->first();
    }

    /**
     * The rules of the campaign as a condition of the new engine.
     *
     * Campaign: OR(rules, operators) AND count rules. A rule is an AND of its
     * filters; an operator is an AND / OR of its rules and operators. Empty
     * rules and operators select nothing and restrict nothing: they are left out,
     * as the campaign does.
     *
     * @return array|null null when the campaign has no rule
     */
    public function tree(Campaign $campaign): ?array
    {
        $rules = $campaign->rules()->get();

        $alternatives = [];

        foreach ($rules->where('type', 'filter') as $rule) {
            if ($leaf = $this->leaf($rule)) {
                $alternatives[] = $leaf;
            }
        }

        foreach ($campaign->operators as $operator) {
            if ($group = $this->group($operator)) {
                $alternatives[] = $group;
            }
        }

        $items = [];

        if ($alternatives) {
            $items[] = ['op' => 'or', 'items' => $alternatives];
        }

        // "Count" rules apply to the whole selection
        foreach ($rules->where('type', 'count') as $rule) {
            if ($leaf = $this->leaf($rule)) {
                $items[] = $leaf;
            }
        }

        return $items ? ['op' => 'and', 'items' => $items] : null;
    }

    protected function leaf(CampaignRule $rule): ?array
    {
        return $rule->rule ? ['filters' => $rule->rule] : null;
    }

    protected function group(CampaignOperator $operator, array $path = []): ?array
    {
        // A cycle of operators would never end
        if (in_array($operator->id, $path, true)) {
            return null;
        }

        $items = [];

        foreach ($operator->rules as $rule) {
            if ($leaf = $this->leaf($rule)) {
                $items[] = $leaf;
            }
        }

        foreach ($operator->operators as $child) {
            if ($group = $this->group($child, [...$path, $operator->id])) {
                $items[] = $group;
            }
        }

        return $items ? ['op' => $operator->and ? 'and' : 'or', 'items' => $items] : null;
    }

    /**
     * The workflow: trigger (enters the filter, once per prospect) -> [wait for
     * the start date -> check the rules again] -> the actions in order -> end.
     */
    public function graph(Campaign $campaign): array
    {
        $tree = $this->tree($campaign);
        $timezone = config('campaign.timezone');
        $nodes = [];
        $edges = [];
        $y = 60;
        $previous = null;
        $link = function (string $to, ?string $branch = null) use (&$previous, &$edges) {
            if ($previous !== null) {
                $edges[] = array_filter(['from' => $previous, 'to' => $to, 'branch' => $branch]);
            }
            $previous = $to;
        };
        $add = function (string $id, string $type, array $config, ?string $name = null) use (&$nodes, &$y, $link) {
            $nodes[] = ['id' => $id, 'type' => $type, 'name' => $name, 'config' => $config, 'position' => ['x' => 80, 'y' => $y]];
            $y += 110;
            $link($id);
        };

        $add('trigger', 'trigger', $tree + [
            'trigger' => 'prospect.filter_entered',
            // The prospects already processed by the campaign must not start again
            'once_per_subject' => true,
        ]);

        // A campaign starts at its date: prospects that match before wait for it,
        // and are checked again when it comes (the campaign evaluates the rules when it runs)
        $start = Carbon::parse($campaign->date, $timezone);
        $waits = $start->gt(now($timezone));

        if ($waits) {
            $add('wait', 'wait', ['until_date' => $start->format('Y-m-d H:i'), 'timezone' => $timezone], 'Date de début de la campagne');
            $add('check', 'condition', $tree, 'Toujours dans les règles ?');
        }

        foreach ($campaign->actions()->orderBy('campaign_action.id')->get()->values() as $i => $action) {
            $id = 'action' . ($i + 1);

            // The legacy handlers carry on with the next action when one fails: so does the workflow
            $add($id, 'action', ['action' => $action->action, 'value' => $action->value ?: [], 'on_error' => 'continue']);

            if ($waits && $i === 0) {
                // The first action follows the "yes" way of the check
                $edges = array_map(fn ($edge) => $edge['to'] === $id ? $edge + ['branch' => 'yes'] : $edge, $edges);
            }
        }

        $add('end', 'end', []);

        if ($waits) {
            $nodes[] = ['id' => 'end_no', 'type' => 'end', 'name' => 'Plus dans les règles', 'config' => [], 'position' => ['x' => 380, 'y' => 60 + 110 * 2]];
            $edges[] = ['from' => 'check', 'to' => 'end_no', 'branch' => 'no'];
        }

        return ['nodes' => $nodes, 'edges' => $edges];
    }

    /**
     * What is different for the user once converted
     *
     * @return string[]
     */
    protected function notes(Campaign $campaign, array $tree): array
    {
        $notes = [
            'Un prospect est traité dès qu\'il correspond aux règles, sans attendre le prochain passage planifié (' . $campaign->frequency . ').',
            'Chaque prospect n\'est traité qu\'une fois, comme avant.',
            'Les prospects qui correspondent déjà et que la campagne n\'a pas encore traités le seront à la bascule.',
        ];

        if (Carbon::parse($campaign->date, config('campaign.timezone'))->gt(now(config('campaign.timezone')))) {
            $notes[] = 'L\'automatisation attend la date de début (' . $campaign->date . ') avant d\'agir.';
        }

        if ($this->usesTime($tree)) {
            $notes[] = 'Des critères dépendent du temps (ancienneté...) : ils sont évalués quand le prospect est modifié, pas à chaque passage.';
        }

        return $notes;
    }

    /**
     * Does a criterion depend on the clock ("created X days ago")?
     */
    protected function usesTime(array $node): bool
    {
        foreach ($node['items'] ?? [] as $item) {
            if (isset($item['items']) && $this->usesTime($item)) {
                return true;
            }

            foreach (array_keys($item['filters'] ?? []) as $name) {
                if (preg_match('/days|ago|before|after|date|started|starts|ended/i', $name)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function result(string $status, array $reasons = [], array $notes = [], ?int $workflowId = null): array
    {
        return ['status' => $status, 'reasons' => $reasons, 'notes' => $notes, 'workflow_id' => $workflowId];
    }
}
