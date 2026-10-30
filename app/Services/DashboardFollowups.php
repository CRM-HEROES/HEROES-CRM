<?php

namespace App\Services;

use App\Models\Label;
use App\Models\Project;
use App\Models\Prospect;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DashboardFollowups
{
    public function __construct(protected Project $project)
    {
    }

    public function labels(): Collection
    {
        return Label::query()
            ->whereHas('category', fn ($query) => $query
                ->where('project_id', $this->project->id)->where('for', 'prospect'))
            ->with('category:id,name')
            ->orderBy('name')
            ->get(['labels.id', 'labels.name', 'labels.category_id']);
    }

    /** Count assignments by their actor and action date, even after a removal. */
    public function counts(Collection $labels, array $userIds, string $from, string $to): array
    {
        if ($labels->isEmpty() || !$userIds) {
            return [];
        }

        $history = app(ProspectLabelHistory::class);
        $morph = (new Prospect())->getMorphClass();
        $revisions = DB::table('revisions')
            ->join('prospects as p', 'p.id', '=', 'revisions.revisionable_id')
            ->join('labels', fn ($join) => $join->whereIn('labels.id', $labels->modelKeys()))
            ->where('p.project_id', $this->project->id)->whereNull('p.deleted_at')
            ->where('revisions.revisionable_type', $morph)
            ->whereIn('revisions.user_id', $userIds)
            ->whereBetween('revisions.created_at', [$from, $to]);
        $this->whereCategoryKey($revisions, $labels);
        $history->whereRecordedAssignment($revisions);
        $revisions->select('labels.id as label_id', 'revisions.user_id as user_id');

        // Imports/bulk actions can have a pivot but no revision yet. Include it
        // only until its original assignment is represented in the history.
        $assignments = DB::table('prospect_label')
            ->join('prospects as p', 'p.id', '=', 'prospect_label.prospect_id')
            ->join('labels', 'labels.id', '=', 'prospect_label.label_id')
            ->where('p.project_id', $this->project->id)->whereNull('p.deleted_at')
            ->whereIn('labels.id', $labels->modelKeys())
            ->whereIn('prospect_label.creator_id', $userIds)
            ->whereBetween('prospect_label.created_at', [$from, $to])
            ->whereNotExists(function ($query) use ($labels, $history, $morph) {
                $query->selectRaw('1')->from('revisions')
                    ->whereColumn('revisions.revisionable_id', 'prospect_label.prospect_id')
                    ->where('revisions.revisionable_type', $morph)
                    ->whereColumn('revisions.created_at', 'prospect_label.created_at')
                    ->whereColumn('revisions.user_id', 'prospect_label.creator_id');
                $this->whereCategoryKey($query, $labels);
                $history->whereRecordedAssignment($query);
            })
            ->select('labels.id as label_id', 'prospect_label.creator_id as user_id');

        $totals = DB::query()->fromSub($revisions->unionAll($assignments), 'followup_actions')
            ->select('label_id', 'user_id')->selectRaw('COUNT(*) as total')
            ->groupBy('label_id', 'user_id')->get();

        $counts = [];
        foreach ($totals as $total) {
            $counts[$total->user_id]['followup_' . $total->label_id] = (int) $total->total;
        }

        return $counts;
    }

    private function whereCategoryKey($query, Collection $labels): void
    {
        $query->where(function ($query) use ($labels) {
            foreach ($labels->pluck('category_id')->unique() as $categoryId) {
                $query->orWhere(fn ($query) => $query
                    ->where('labels.category_id', $categoryId)
                    ->where('revisions.key', 'category->' . $categoryId));
            }
        });
    }
}
