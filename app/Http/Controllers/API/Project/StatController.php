<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\UserSetting;
use App\Utils\Stat\Chart\EventsCount as ChartEventsCount;
use App\Utils\Stat\Chart\FilesCount as ChartFilesCount;
use App\Utils\Stat\Chart\InteractionsCount as ChartInteractionsCount;
use App\Utils\Stat\Chart\LabelProspectsCount as ChartLabelProspectsCount;
use App\Utils\Stat\Chart\MessagesCount as ChartMessagesCount;
use App\Utils\Stat\Chart\OrdersCount as ChartOrdersCount;
use App\Utils\Stat\Chart\OrdersStepsCount as ChartOrdersStepsCount;
use App\Utils\Stat\Chart\OrdersProductsCount as ChartOrdersProductsCount;
use App\Utils\Stat\Chart\SmsCount as ChartSmsCount;
use App\Utils\Stat\DailyChart\EventsCount as DailyChartEventsCount;
use App\Utils\Stat\DailyChart\FilesCount as DailyChartFilesCount;
use App\Utils\Stat\DailyChart\InteractionsCount as DailyChartInteractionsCount;
use App\Utils\Stat\DailyChart\InteractionsDuration as DailyChartInteractionsDuration;
use App\Utils\Stat\DailyChart\LabelProspectsCount as DailyChartLabelProspectsCount;
use App\Utils\Stat\DailyChart\MessagesCount as DailyChartMessagesCount;
use App\Utils\Stat\DailyChart\OrdersCount as DailyChartOrdersCount;
use App\Utils\Stat\DailyChart\OrdersTotal as DailyChartOrdersTotal;
use App\Utils\Stat\DailyChart\ProspectsCount as DailyChartProspectsCount;
use App\Utils\Stat\DailyChart\SmsCount as DailyChartSmsCount;
use App\Utils\Stat\Value\EventsCount;
use App\Utils\Stat\Value\FilesCount;
use App\Utils\Stat\Value\InteractionsCount;
use App\Utils\Stat\Value\InteractionsDurations;
use App\Utils\Stat\Value\LabelProspectsCount;
use App\Utils\Stat\Value\MessagesCount;
use App\Utils\Stat\Value\OrdersCount;
use App\Utils\Stat\Value\OrdersTotal;
use App\Utils\Stat\Value\ProspectsCount;
use App\Utils\Stat\Value\SmsCount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StatController extends Controller
{
    /**
     * Retrieve all needed data for the stat chart
     */
    public function index(Request $request, Project $project)
    {
        $this->validate($request, [
            'from' => 'required',
            'to' => 'required',
        ]);

        $from = $request->input('from');
        $to = $request->input('to');

        // Stat values
        // Per ex:
        // - Total prospects
        // - New prospects
        // - SMS count
        // - Interactions count
        // - Orders count
        // - Orders price total
        // - Commissions total
        // - Events count
        // - Files count
        // - Messages count
        // - New Prospects per label
        // - Total Prospects per label
        $statValues = $this->blocValues($project);

        $values = [];
        foreach ($statValues as $v) {
            $value = $v['stat']->value($project->id);
            $unity = $v['stat']->unity();

            $values[] = [
                'key' => $v['key'],
                'name' => $v['name'],
                'color' => $v['color'],
                'bgcolor' => $v['bgcolor'],
                'value' => $value,
                'unity' => $unity,
            ];
        }

        // Stat chart
        // Per ex:
        // - Total prospects per day/country
        // - New prospects per day/country
        // - SMS count per day
        // - Interactions count per day
        // - Orders count per day
        // - Orders price total per day
        // - Commissions total per day
        // - Events count per day
        // - Files count per day
        // - Messages count per day
        // - New Prospects per label per day
        // - Total Prospects per label per day
        $statCharts = $this->blocCharts($project);

        $charts = [];
        foreach ($statCharts as $v) {
            $value = $v['stat']->value($project, $from, $to);

            $charts[] = [
                'name' => $v['name'],
                'type' => $v['type'],
                'value' => $value,
            ];
        }

        return [
            'values' => $values,
            'charts' => $charts
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Project $project, $key)
    {
        $this->validate($request, [
            'from' => 'required',
            'to' => 'required',
        ]);

        $from = $request->input('from');
        $to = $request->input('to');

        return $this->dailyStat($project, $key, $from, $to);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        $charts = $this->statSettings($project);

        $key = "stat-bloc-chart";
        $chart = $request->input(
            'chart',
        );

        $charts[] = $chart;

        UserSetting::updateOrCreate([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'key' => $key,
        ], [
            'value' => $charts,
            'creator_id' => auth()->id(),
        ]);

        return $this->blocChart($chart)['stat']->value($project, "2025-02-01", "2025-02-21");
    }

    /**
     * Display the specified resource.
     */
    public function chart(Request $request, Project $project, $index)
    {
        $this->validate($request, [
            'from' => 'required',
            'to' => 'required',
        ]);

        $from = $request->input('from');
        $to = $request->input('to');

        $charts = $this->statSettings($project);

        return $this->blocChart($charts[$index])['stat']->value($project, $from, $to);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, $index)
    {
        $charts = $this->statSettings($project);

        if ($index >= count($charts)) {
            abort(404);
        }

        $charts = array_filter($charts, function($v, $k) use ($index) {
            return $k != $index;
        }, ARRAY_FILTER_USE_BOTH);

        $key = "stat-bloc-chart";

        UserSetting::where([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'key' => $key,
        ])->update([
            'value' => $charts,
        ]);

        return ['message' => trans('common.success.deleted_resource')];
    }
    
    /**
     * 
     */
    protected function dailyStat(Project $project, $key, $from, $to)
    {
        $results = [];
        
        $stat = DB::table('stats')
            ->where('project_id', $project->id)
            ->whereNull('user_id')
            ->where('key', $key)
            ->where('date', '<', $from)
            ->orderBy('date', 'desc')
            ->first();

        $previousStatValue = $stat ? $stat->value : 0;

        $stats = DB::table('stats')
            ->where('project_id', $project->id)
            ->whereNull('user_id')
            ->where('key', $key)
            ->where('date', '>=', $from)
            ->where('date', '<=', $to)
            ->get();

        for (
            $date = new \Carbon\Carbon($from), 
            $lastDate = new \Carbon\Carbon($to);
            $date->lte($lastDate);
            $date = $date->addDay()
        ) {
            $dateValue = $date->format("Y-m-d");

            if ($stat = $stats->where('date', $dateValue)->first()) {
                $value = $stat->value;
            } else {
                $value = $previousStatValue;
            }

            $results[$dateValue] = $previousStatValue = $value;
        }

        return $results;
    }

    /**
     * Display the specified resource.
     */
    public function country(Request $request, Project $project, $key)
    {
        $this->validate($request, [
            'date' => 'required',
        ]);

        $results = [];

        // Stat date
        $date = $request->input('date');

        // List of the current project countries
        $countries = DB::table('prospects')
            ->distinct()
            ->select('country')
            ->where("project_id", $project->id)
            ->where('country', '!=', '')
            ->whereNotNull('country')
            ->get()
            ->map(function($country) {
                return $country->country;
            });

        foreach ($countries as $country) {
            $stat = DB::table('stats')
                ->select('value')
                ->where('project_id', $project->id)
                ->whereNull('user_id')
                ->where('key', $key . ".country." . $country)
                ->where('date', '<=', $date)
                ->orderBy('date', 'desc')
                ->first();
    
            $results[$country] = $stat ? $stat->value : 0;
        }

        return $results;
    }

    /**
     * Display the specified resource.
     */
    public function prospectRelation(Request $request, Project $project, $key)
    {
        $this->validate($request, [
            'date' => 'required',
        ]);

        $results = [];

        // Stat date
        $date = $request->input('date');

        switch ($key) {
            case "prospect.event.new":
            case "prospect.event.total":
                $keys = $this->prospectsRelationsEvent($project, $key);
                break;
            case "prospect.message.new":
            case "prospect.message.total":
                $keys = $this->prospectsRelationsMessage($project, $key);
                break;
            case "prospect.file.new":
            case "prospect.file.total":
                $keys = $this->prospectsRelationsFile($project, $key);
                break;
            case "prospect.order.new":
            case "prospect.order.total":
                $keys = $this->prospectsRelationsOrder($project, $key);
                break;
            case "prospect.sms.new":
            case "prospect.sms.total":
                $keys = $this->prospectsRelationsSms($project, $key);
                break;
            case "prospect.interaction.new":
            case "prospect.interaction.total":
                $keys = $this->prospectsRelationsInteraction($project, $key);
                break;
            default:
                return [];
        }

        foreach ($keys as $k => $value) {
            $stat = DB::table('stats')
                ->select('value')
                ->where('project_id', $project->id)
                ->whereNull('user_id')
                ->where('key', $k)
                ->where('date', '<=', $date)
                ->orderBy('date', 'desc')
                ->first();
    
            $results[] = [
                "name" => $value['name'],
                "color" => $value['color'],
                "value" => $stat ? $stat->value : 0,
                "filter" => $value['filter'],
            ];
        }

        return $results;
    }

    /**
     */
    public function prospectsRelationsEvent(Project $project, $key)
    {
        $keys = [];

        foreach ($project->calendars as $calendar) {
            $keys[$key . ".calendar." . $calendar->id] = [
                'name' => $calendar->name,
                'color' => $calendar->bgcolor,
                'filter' => ["withEvents" => ["withCalendars" => [$calendar->id]]]
            ];
        }

        return $keys;
    }

    /**
     */
    public function prospectsRelationsMessage(Project $project, $key)
    {
        $keys = [];

        foreach ($project->threads as $thread) {
            $keys[$key . ".thread." . $thread->id] = [
                'name' => $thread->name,
                'color' => $thread->bgcolor,
                'filter' => ["withMessages" => [$thread->id]]
            ];
        }

        return $keys;
    }

    /**
     */
    public function prospectsRelationsFile(Project $project, $key)
    {
        $keys = [];

        foreach ($project->folders as $folder) {
            $keys[$key . ".folder." . $folder->id] = [
                'name' => $folder->name,
                'color' => $folder->bgcolor,
                'filter' => ["withFiles" => [$folder->id]]
            ];
        }

        return $keys;
    }

    /**
     */
    public function prospectsRelationsOrder(Project $project, $key)
    {
        $keys = [];

        foreach ($project->orderStatuses as $orderStatus) {
            $keys[$key . ".status." . $orderStatus->id] = [
                'name' => $orderStatus->name,
                'color' => $orderStatus->bgcolor,
                'filter' => ["withOrders" => ["withStatuses" => [$orderStatus->id]]]
            ];
        }

        return $keys;
    }

    /**
     */
    public function prospectsRelationsSms(Project $project, $key)
    {
        
        $keys = [];

        foreach ([
            'smsbox' => [
                'name' => "SMSBOX",
                'color' => "rgb(255, 99, 132)"
            ],
            'ultramsg' => [
                'name' => "UltraMsg",
                'color' => "rgb(54, 162, 235)"
            ],
            'mtarget' => [
                'name' => "MTarget",
                'color' => "rgb(0, 180, 160)"
            ],
            'brevo' => [
                'name' => "Brevo",
                'color' => "rgb(11, 153, 108)"
            ],
            'telephone' => [
                'name' => "Téléphone",
                'color' => "rgb(255, 79, 55)"
            ],
            'whatsapp' => [
                'name' => "Whatsapp",
                'color' => "rgb(255, 205, 86)"
            ],
            'ringover' => [
                'name' => "Ringover",
                'color' => "rgb(0, 201, 76)"
            ],
            'kavkom' => [
                'name' => "Kavkom",
                'color' => "rgb(142, 36, 170)"
            ]
        ] as $source => $value) {
            $keys[$key . ".source." . $source] = array_merge($value, [
                'filter' => ["withSms" => ["withSources" => [$source]]]
            ]);
        }

        return $keys;
    }

    /**
     */
    public function prospectsRelationsInteraction(Project $project, $key)
    {
        
        $keys = [];

        foreach ([
            'aircall' => [
                'name' => "Aircall",
                'color' => "rgb(255, 99, 132)"
            ],
            'ringover' => [
                'name' => "Ringover",
                'color' => "rgb(0, 201, 76)"
            ],
            'telephone' => [
                'name' => "Téléphone",
                'color' => "rgb(255, 79, 55)"
            ]
        ] as $source => $value) {
            $keys[$key . ".source." . $source] = array_merge($value, [
                'filter' => ["withInteractions" => ["withSources" => [$source]]]
            ]);
        }

        return $keys;
    }

    /**
     */
    protected function blocValues(Project $project)
    {
        $key = "stat-bloc-value";
        
        $setting = UserSetting::where([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'key' => $key,
        ])
        ->first();

        if (!$setting) {
            $setting = config('default-settings.' . $key);
        } else {
            $setting = json_decode(json_encode($setting->value), true);
        }

        return array_filter(array_map(function($blocValue) {
            $stat = null;

            if ($blocValue['key'] == "prospects") {
                $stat = new ProspectsCount();
            } else if ($blocValue['key'] == "interactions") {
                $stat = new InteractionsCount();
            } else if ($blocValue['key'] == "interactions-duration") {
                $stat = new InteractionsDurations();
            } else if ($blocValue['key'] == "orders") {
                $stat = new OrdersCount();
            } else if ($blocValue['key'] == "orders-total") {
                $stat = new OrdersTotal();
            } else if ($blocValue['key'] == "sms") {
                $stat = new SmsCount();
            } else if ($blocValue['key'] == "events") {
                $stat = new EventsCount();
            } else if ($blocValue['key'] == "files") {
                $stat = new FilesCount();
            } else if ($blocValue['key'] == "messages") {
                $stat = new MessagesCount();
            } else if (Str::startsWith($blocValue['key'], 'label.prospects.')) {
                $stat = new LabelProspectsCount(str_replace('label.prospects.', '', $blocValue['key']));
            }

            if (!$stat) {
                return null;
            }

            return array_merge($blocValue, ['stat' => $stat]);
        }, $setting));
    }
    
    /**
     */
    protected function statSettings(Project $project)
    {
        $key = "stat-bloc-chart";

        $setting = UserSetting::where([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'key' => $key,
        ])
        ->first();

        if (!$setting) {
            $setting = config('default-settings.' . $key);
        } else {
            $setting = json_decode(json_encode($setting->value), true);
        }

        return $setting;
    }
    
    /**
     */
    protected function blocCharts(Project $project)
    {
        return array_filter(array_map(function($blocValue) {
            return $this->blocChart($blocValue);
        }, $this->statSettings($project)));
    }
    
    /**
     */
    protected function blocChart($blocValue)
    {
        $stat = null;
        $key = $blocValue['key'];
        $type = $blocValue['type'];

        if (in_array($type, ["pie", "funnel"])) {
                
            if ($key == "interactions") {
                $stat = new ChartInteractionsCount();
            } else if ($key == "orders") {
                $stat = new ChartOrdersCount();
            } else if ($key == "orders-steps") {
                $stat = new ChartOrdersStepsCount();
            } else if ($key == "orders-products") {
                $stat = new ChartOrdersProductsCount();
            } else if ($key == "sms") {
                $stat = new ChartSmsCount();
            } else if ($key == "events") {
                $stat = new ChartEventsCount();
            } else if ($key == "files") {
                $stat = new ChartFilesCount();
            } else if ($key == "messages") {
                $stat = new ChartMessagesCount();
            } else if (Str::startsWith($key, 'label.prospects.')) {
                $stat = new ChartLabelProspectsCount($blocValue['labels']);
            }

        } else if (in_array($type, ["vbar", "hbar", "line", "calendar", "radar"])) {
                
            if ($key == "prospects") {
                $stat = new DailyChartProspectsCount();
            } else if ($key == "interactions") {
                $stat = new DailyChartInteractionsCount();
            } else if ($key == "interactions-duration") {
                $stat = new DailyChartInteractionsDuration();
            } else if ($key == "orders") {
                $stat = new DailyChartOrdersCount();
            } else if ($key == "orders-total") {
                $stat = new DailyChartOrdersTotal();
            } else if ($key == "sms") {
                $stat = new DailyChartSmsCount();
            } else if ($key == "events") {
                $stat = new DailyChartEventsCount();
            } else if ($key == "files") {
                $stat = new DailyChartFilesCount();
            } else if ($key == "messages") {
                $stat = new DailyChartMessagesCount();
            } else if (Str::startsWith($key, 'label.prospects.')) {
                $stat = new DailyChartLabelProspectsCount($blocValue['labels']);
            }

        }

        if (!$stat) {
            return null;
        }

        return array_merge($blocValue, ['stat' => $stat]);
    }
    
}