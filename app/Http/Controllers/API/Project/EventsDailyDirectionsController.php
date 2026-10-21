<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventsDailyDirectionsController extends Controller
{
    /**
     * Read cached travel summaries without starting a Google Maps calculation.
     */
    public function index(Request $request, Project $project)
    {
        abort_unless($user = $request->user(), 401);

        $validated = $request->validate([
            'pairs' => ['required', 'array', 'min:1', 'max:500'],
            'pairs.*' => ['required', 'array:user_id,direction_at'],
            'pairs.*.user_id' => ['required', 'integer', 'min:1'],
            'pairs.*.direction_at' => ['required', 'date_format:Y-m-d'],
        ]);

        $pairs = collect($validated['pairs'])
            ->map(function ($pair) {
                return ['user_id' => (int) $pair['user_id'], 'direction_at' => $pair['direction_at']];
            })
            ->unique(function ($pair) {
                return $pair['user_id'] . ':' . $pair['direction_at'];
            })
            ->values();
        $userIds = $pairs->pluck('user_id')->unique()->values();

        // A permission for another project must never bypass project membership.
        $projectUserIds = $project->users()->whereIn('users.id', $userIds)->pluck('users.id');
        abort_unless($userIds->diff($projectUserIds)->isEmpty(), 404);

        if (!$user->can('projectUserAgenda', $project)) {
            $prefix = 'all.project.user.agenda.';
            $visibleUserIds = $user->getAllPermissions()
                ->pluck('name')
                ->filter(function ($permission) use ($prefix) {
                    return Str::startsWith($permission, $prefix);
                })
                ->map(function ($permission) use ($prefix) {
                    return Str::after($permission, $prefix);
                })
                ->filter(function ($userId) {
                    return ctype_digit((string) $userId);
                })
                ->map(function ($userId) {
                    return (int) $userId;
                })
                ->push($user->id);

            abort_unless($userIds->diff($visibleUserIds)->isEmpty(), 403);
        }

        // Group dates per user to query only the requested pairs in one read.
        // The existing cache is global to user/date, including cross-project trips.
        $directions = $this->selectDirectionSummary(DB::table('user_events_daily_directions'))
            ->where(function ($query) use ($pairs) {
                foreach ($pairs->groupBy('user_id') as $userId => $userPairs) {
                    $query->orWhere(function ($query) use ($userId, $userPairs) {
                        $query->where('user_id', $userId)
                            ->whereIn('direction_at', $userPairs->pluck('direction_at')->all());
                    });
                }
            })
            ->get()
            ->keyBy(function ($direction) {
                return $direction->user_id . ':' . $direction->direction_at;
            });

        return ['data' => $pairs->map(function ($pair) use ($directions) {
            $direction = $directions->get($pair['user_id'] . ':' . $pair['direction_at']);
            $summary = array_merge($pair, ['status' => 'missing', 'events' => [], 'legs' => []]);

            if (!$direction) {
                return $summary;
            }

            $eventIds = json_decode($direction->events ?? '[]', true);
            $validEventIds = is_array($eventIds) && array_is_list($eventIds);
            $normalizedEventIds = [];
            foreach ($validEventIds ? $eventIds : [] as $id) {
                if ((!is_int($id) && !(is_string($id) && ctype_digit($id))) ||
                    ($normalizedId = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) === false) {
                    $validEventIds = false;
                    break;
                }
                $normalizedEventIds[] = $normalizedId;
            }
            $summary['events'] = $validEventIds ? $normalizedEventIds : [];

            if ($direction->searching) {
                $summary['status'] = 'pending';

                return $summary;
            }

            $result = $this->directionSummary($direction);
            if (!$validEventIds || $result['status'] !== 'OK') {
                $summary['status'] = 'empty';

                return $summary;
            }

            $summary['status'] = 'ready';
            foreach ($result['legs'] as $leg) {
                $summary['legs'][] = [
                    'distance' => $this->metric($leg['distance'] ?? []),
                    'duration' => $this->metric($leg['duration'] ?? []),
                ];
            }

            return $summary;
        })->all()];
    }

    protected function selectDirectionSummary(Builder $query): Builder
    {
        $query->select('user_id', 'direction_at', 'searching', 'events');

        if ($query->getConnection()->getDriverName() !== 'mysql') {
            // SQLite fixtures exercise the same response normalization in tests.
            return $query->addSelect('direction');
        }

        // Project inside MySQL: hundreds of full Google responses (steps,
        // polylines and addresses) must never be transferred into PHP memory.
        // CASE also supports legacy text columns containing malformed JSON.
        foreach ([
            'direction_status' => '$.status',
            'distances' => '$.routes[0].legs[*].distance',
            'durations' => '$.routes[0].legs[*].duration',
        ] as $alias => $path) {
            $extract = "JSON_EXTRACT(direction, '{$path}')";
            if ($alias === 'direction_status') {
                $extract = "JSON_UNQUOTE({$extract})";
            }
            $query->selectRaw("CASE WHEN searching = 0 AND JSON_VALID(direction) THEN {$extract} END AS {$alias}");
        }
        $query->selectRaw("CASE WHEN searching = 0 AND JSON_VALID(direction) THEN JSON_LENGTH(JSON_EXTRACT(direction, '$.routes[0].legs')) END AS leg_count");

        return $query;
    }

    protected function directionSummary(object $direction): array
    {
        if (property_exists($direction, 'direction_status')) {
            $distances = json_decode($direction->distances ?? '[]', true);
            $durations = json_decode($direction->durations ?? '[]', true);
            $distances = is_array($distances) ? $distances : [];
            $durations = is_array($durations) ? $durations : [];
            $legCount = (int) ($direction->leg_count ?? 0);
            // A wildcard omits missing properties. Reject incomplete arrays so
            // that one leg's duration can never be shown against another event.
            if (count($distances) !== $legCount || count($durations) !== $legCount) {
                return ['status' => null, 'legs' => []];
            }
            $legs = [];
            for ($index = 0; $index < $legCount; $index++) {
                $legs[] = [
                    'distance' => $distances[$index] ?? [],
                    'duration' => $durations[$index] ?? [],
                ];
            }

            return ['status' => $direction->direction_status, 'legs' => $legs];
        }

        $result = json_decode($direction->direction ?? '', true);
        $legs = is_array($result) ? data_get($result, 'routes.0.legs', []) : [];

        return [
            'status' => is_array($result) ? ($result['status'] ?? null) : null,
            'legs' => is_array($legs) ? array_map(function ($leg) {
                return is_array($leg) ? $leg : [];
            }, array_values($legs)) : [],
        ];
    }

    protected function metric($metric): array
    {
        $metric = is_array($metric) ? $metric : [];

        return [
            'text' => is_string($metric['text'] ?? null) ? $metric['text'] : '',
            'value' => is_numeric($metric['value'] ?? null) ? (float) $metric['value'] : 0,
        ];
    }
}
