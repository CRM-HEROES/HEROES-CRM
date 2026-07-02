<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\StatChart;
use App\Utils\StatChart\EventsList;
use App\Utils\StatChart\NewCommissionsPerUserPerDay;
use App\Utils\StatChart\NewEventsPerDay;
use App\Utils\StatChart\NewFilesPerDay;
use App\Utils\StatChart\NewInteractionsPerDay;
use App\Utils\StatChart\NewMessagesPerDay;
use App\Utils\StatChart\NewOrdersPerDay;
use App\Utils\StatChart\NewOrdersPricePerDay;
use App\Utils\StatChart\NewProspectsPerDay;
use App\Utils\StatChart\NewProspectsPerLabelPerDay;
use App\Utils\StatChart\NewSmsPerDay;
use App\Utils\StatChart\TotalCommissionsPerUserPerDay;
use App\Utils\StatChart\TotalEventsPerDay;
use App\Utils\StatChart\TotalFilesPerDay;
use App\Utils\StatChart\TotalInteractionsPerDay;
use App\Utils\StatChart\TotalMessagesPerDay;
use App\Utils\StatChart\TotalOrdersPerDay;
use App\Utils\StatChart\TotalOrdersPricePerDay;
use App\Utils\StatChart\TotalProspectsPerCountry;
use App\Utils\StatChart\TotalProspectsPerDay;
use App\Utils\StatChart\TotalProspectsPerLabel;
use App\Utils\StatChart\TotalProspectsPerLabelPerDay;
use App\Utils\StatChart\TotalSmsPerDay;
use Illuminate\Http\Request;

class StatChartController extends Controller
{
    private $statChartDatas = [
        'events-list' => EventsList::class,
        'new-commissions-per-user-per-day' => NewCommissionsPerUserPerDay::class,
        'new-events-per-day' => NewEventsPerDay::class,
        'new-events-per-day' => NewEventsPerDay::class,
        'new-files-per-day' => NewFilesPerDay::class,
        'new-interactions-per-day' => NewInteractionsPerDay::class,
        'new-messages-per-day' => NewMessagesPerDay::class,
        'new-orders-per-day' => NewOrdersPerDay::class,
        'new-orders-price-per-day' => NewOrdersPricePerDay::class,
        'new-prospects-per-day' => NewProspectsPerDay::class,
        'new-prospects-per-label-per-day' => NewProspectsPerLabelPerDay::class,
        'new-sms-per-day' => NewSmsPerDay::class,
        'total-commissions-per-user-per-day' => TotalCommissionsPerUserPerDay::class,
        'total-events-per-day' => TotalEventsPerDay::class,
        'total-files-per-day' => TotalFilesPerDay::class,
        'total-interactions-per-day' => TotalInteractionsPerDay::class,
        'total-messages-per-day' => TotalMessagesPerDay::class,
        'total-orders-per-day' => TotalOrdersPerDay::class,
        'total-orders-price-per-day' => TotalOrdersPricePerDay::class,
        'total-prospects-per-country' => TotalProspectsPerCountry::class,
        'total-prospects-per-day' => TotalProspectsPerDay::class,
        'total-prospects-per-label' => TotalProspectsPerLabel::class,
        'total-prospects-per-label-per-day' => TotalProspectsPerLabelPerDay::class,
        'total-sms-per-day' => TotalSmsPerDay::class,
    ];

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('', $project), 404);

        $this->validate($request, [
            'name' => 'required',
        ]);

        return $project->statCharts()->create(array_merge(
            $request->only(
                'name',
                'description',
                'type',
                'chart_type',
                'labels',
                'options',
            ),
            [
                'creator_id' => auth()->id(),
            ], 
        ));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Project $project, StatChart $statChart)
    {
        abort_unless($project->id == $statChart->project_id, 404);
        abort_unless($statChart->creator_id == auth()->id(), 404);

        if (isset($this->statChartDatas[$statChart->type])) {
            $now = \Carbon\Carbon::now();
            $firstOfMonth = $now->firstOfMonth()->format("Y-m-d");
            $lastOfMonth = $now->lastOfMonth()->format("Y-m-d");
            
            $className = $this->statChartDatas[$statChart->type];
            $instance = new $className($statChart);
            $data = $instance->data(
                $request->input('from', $firstOfMonth),
                $request->input('to', $lastOfMonth)
            );
        } else {
            $data = [];
        }

        $statChart->data = $data;

        return $statChart;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, StatChart $statChart)
    {
        abort_unless($statChart->creator_id == auth()->id(), 404);
        abort_unless($project->id == $statChart->project_id, 404);

        $statChart->update($request->only(
                'name',
                'description',
                'type',
                'chart_type',
                'labels',
                'options',
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, StatChart $statChart)
    {
        abort_unless($project->id == $statChart->project_id, 404);
        abort_unless($statChart->creator_id == auth()->id(), 404);

        $statChart->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
