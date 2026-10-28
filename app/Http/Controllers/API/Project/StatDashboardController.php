<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\UserSetting;
use App\Services\CloudTalk;
use App\Services\DashboardRecordings;
use App\Utils\Stat\Builder\DashboardEngine;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Dashboard Builder : configuration (widgets + disposition + filtres globaux)
 * et exécution des requêtes de données.
 */
class StatDashboardController extends Controller
{
    protected const KEY = 'stat-dashboard';

    protected const TYPES = ['bar', 'hbar', 'line', 'multiline', 'pie', 'donut', 'funnel', 'heatmap', 'table', 'kpi', 'combo'];

    /**
     * Catalogue des sources, périodes et options de filtres.
     */
    public function meta(Project $project)
    {
        return (new DashboardEngine($project))->meta();
    }

    /**
     * Configuration enregistrée (ou dashboard par défaut).
     */
    public function show(Request $request, Project $project)
    {
        if ($request->boolean('default')) {
            return $this->defaultDashboard();
        }

        $setting = UserSetting::where([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'key' => self::KEY,
        ])->first();

        if ($setting && is_array($setting->value) && isset($setting->value['widgets'])) {
            return $setting->value;
        }

        return $this->defaultDashboard();
    }

    /**
     * Enregistre toute la configuration du dashboard.
     */
    public function update(Request $request, Project $project)
    {
        $this->validate($request, [
            'widgets' => 'present|array|max:60',
            'widgets.*.id' => 'required|string|max:64',
            'widgets.*.type' => 'required|string|in:' . implode(',', self::TYPES),
            'widgets.*.title' => 'nullable|string|max:120',
            'widgets.*.config' => 'required|array',
            'widgets.*.layout' => 'nullable|array',
            'globalFilters' => 'nullable|array',
        ]);

        $value = [
            'version' => 1,
            'widgets' => array_values($request->input('widgets')),
            'globalFilters' => $request->input('globalFilters', (object) []),
            'enabledFilters' => $request->input('enabledFilters', ['period']),
        ];

        UserSetting::updateOrCreate([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'key' => self::KEY,
        ], [
            'value' => $value,
            'creator_id' => auth()->id(),
        ]);

        return $value;
    }

    /**
     * Exécute la configuration d'un widget (aperçu du wizard ou widget du dashboard).
     */
    public function query(Request $request, Project $project)
    {
        $this->validate($request, [
            'config' => 'required|array',
            'config.type' => 'required|string|in:' . implode(',', self::TYPES),
            'global' => 'nullable|array',
            'runtime' => 'nullable|array',
        ]);

        $config = $request->input('config');

        return (new DashboardEngine($project))->run(
            $config,
            $request->input('global', []),
            $request->input('runtime', [])
        );
    }

    public function recording(Request $request, Project $project, int $userId, CloudTalk $cloudTalk)
    {
        $this->validate($request, [
            'from' => 'required|date_format:Y-m-d',
            'to' => 'required|date_format:Y-m-d|after_or_equal:from',
        ]);
        abort_unless($project->users()->where('users.id', $userId)->exists(), 404);

        try {
            $media = (new DashboardRecordings($project))->media(
                $userId,
                Carbon::parse($request->input('from'))->startOfDay()->toDateTimeString(),
                Carbon::parse($request->input('to'))->endOfDay()->toDateTimeString(),
                $cloudTalk
            );
        } catch (RuntimeException $e) {
            abort(in_array($e->getCode(), [403, 422, 500, 503], true) ? $e->getCode() : 500, $e->getMessage());
        }

        if ($media === null) {
            return response()->noContent()->header('Cache-Control', 'no-store');
        }

        return response($media['body'])
            ->header('Content-Type', $media['content_type'])
            ->header('Cache-Control', 'private, no-store');
    }

    protected function defaultDashboard(): array
    {
        $kpi = fn ($title, $source, $field, $agg, $icon, $color) => [
            'id' => (string) Str::uuid(),
            'type' => 'kpi',
            'title' => $title,
            'config' => [
                'type' => 'kpi',
                'dataSource' => $source,
                'series' => [['field' => $field, 'agg' => $agg]],
                'icon' => $icon,
                'color' => $color,
                'comparison' => 'previous_period',
                'positiveIsGood' => true,
            ],
            'layout' => ['w' => 3, 'h' => 2],
        ];

        return [
            'version' => 1,
            'enabledFilters' => ['period', 'user'],
            'globalFilters' => ['period' => ['preset' => 'last_30_days']],
            'widgets' => [
                $kpi("Appels", 'interactions', 'records', 'count', 'fa-phone', '#6366f1'),
                $kpi("Durée des appels (min)", 'interactions', 'duration', 'sum', 'fa-clock', '#0ea5e9'),
                $kpi("SMS", 'sms', 'records', 'count', 'fa-comment', '#10b981'),
                $kpi("Commandes", 'orders', 'records', 'count', 'fa-shopping-bag', '#f59e0b'),
                [
                    'id' => (string) Str::uuid(),
                    'type' => 'line',
                    'title' => "Activité des appels",
                    'config' => [
                        'type' => 'line',
                        'dataSource' => 'interactions',
                        'dimension' => 'day',
                        'series' => [['field' => 'records', 'agg' => 'count']],
                        'legend' => true,
                        'compare' => true,
                    ],
                    'layout' => ['w' => 12, 'h' => 5],
                ],
                [
                    'id' => (string) Str::uuid(),
                    'type' => 'table',
                    'title' => "Performance des utilisateurs",
                    'config' => [
                        'type' => 'table',
                        'dataSource' => 'users',
                        'columns' => [
                            ['field' => 'name'],
                            ['field' => 'role'],
                            ['field' => 'connection_status'],
                            ['field' => 'call_duration'],
                            ['field' => 'recording'],
                            ['field' => 'performance'],
                        ],
                        'sorting' => ['field' => 'performance', 'direction' => 'desc'],
                        'pagination' => true,
                        'perPage' => 5,
                        'search' => true,
                    ],
                    'layout' => ['w' => 7, 'h' => 6],
                ],
                [
                    'id' => (string) Str::uuid(),
                    'type' => 'donut',
                    'title' => "Appels par source",
                    'config' => [
                        'type' => 'donut',
                        'dataSource' => 'interactions',
                        'dimension' => 'source',
                        'series' => [['field' => 'records', 'agg' => 'count']],
                        'legend' => true,
                        'showLabels' => true,
                    ],
                    'layout' => ['w' => 5, 'h' => 6],
                ],
            ],
        ];
    }
}
