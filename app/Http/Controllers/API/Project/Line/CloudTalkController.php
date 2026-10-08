<?php

namespace App\Http\Controllers\API\Project\Line;

use App\Http\Controllers\Controller;
use App\Models\Interaction;
use App\Models\Line;
use App\Models\Message;
use App\Models\Project;
use App\Models\Prospect;
use App\Services\CloudTalk;
use Carbon\Carbon;
use Illuminate\Http\Request;
use RuntimeException;

class CloudTalkController extends Controller
{
    /**
     * Verify CloudTalk API credentials.
     */
    public function verify(Request $request, Project $project, CloudTalk $cloudTalk)
    {
        $this->authorizeCloudTalkConfig($project);
        $config = $this->credentials($request);

        try {
            $cloudTalk->verifyCredentials($config);
        } catch (RuntimeException $e) {
            return $this->runtimeError($e);
        }

        return [
            'valid' => true,
            'message' => 'Identifiants CloudTalk valides.',
        ];
    }

    /**
     * Fetch CloudTalk agents for the given API credentials.
     */
    public function agents(Request $request, Project $project, CloudTalk $cloudTalk)
    {
        $this->authorizeCloudTalkConfig($project);
        $config = $this->credentials($request);

        try {
            $agents = $cloudTalk->agents($config);
        } catch (RuntimeException $e) {
            return $this->runtimeError($e);
        }

        return [
            'valid' => true,
            'agents' => $agents,
        ];
    }

    /**
     * Create an outbound CloudTalk call for the authenticated agent.
     */
    public function store(Request $request, Project $project, CloudTalk $cloudTalk)
    {
        $this->validate($request, [
            'number' => 'required|string',
            'line_id' => 'nullable|integer',
            'interaction_id' => 'nullable|integer',
        ]);

        $line = $this->resolveLine($request, $project);

        if (!$line) {
            return response()->json([
                'message' => 'Aucune ligne CloudTalk affectee a votre utilisateur.',
            ], 404);
        }

        try {
            $number = $cloudTalk->formatCalleeNumber($request->input('number'));
            $response = $cloudTalk->makeCall($line, $number);
        } catch (RuntimeException $e) {
            return $this->runtimeError($e);
        }

        $responseData = data_get($response, 'responseData');
        $callId = $cloudTalk->callId($response);
        $interaction = $this->updateInteractionFromCallResponse(
            $request,
            $project,
            $number,
            is_array($responseData) ? $responseData : null,
            $callId
        );

        return [
            'message' => 'Appel CloudTalk lance.',
            'number' => $number,
            'call_id' => $callId,
            'responseData' => $responseData,
            'interaction' => $interaction,
        ];
    }

    /**
     * Resolve a CloudTalk call number to the current user's prospect context.
     */
    public function lookup(Request $request, Project $project)
    {
        $this->validate($request, [
            'number' => 'required|string',
        ]);

        $number = $request->input('number');
        $normalized = $this->normalizePhone($number);

        if (strlen($normalized) < 6) {
            return [
                'number' => $number,
                'prospect' => null,
                'threads' => [],
                'messages' => [],
            ];
        }

        $prospect = $this->findProspectByPhone($project, $normalized);

        if (!$prospect) {
            return [
                'number' => $number,
                'prospect' => null,
                'threads' => [],
                'messages' => [],
            ];
        }

        $threads = $project
            ->threads()
            ->select('id', 'name', 'color', 'bgcolor', 'order')
            ->whereHas('messages', function($query) use($prospect) {
                $query->where('prospect_id', $prospect->id);
            })
            ->withCount([
                'messages as messages_count' => function($query) use($prospect) {
                    $query->where('prospect_id', $prospect->id);
                },
                'messages as user_messages_count' => function($query) use($prospect) {
                    $query
                        ->where('prospect_id', $prospect->id)
                        ->whereHas('users', function($query) {
                            $query->where('id', auth()->id());
                        });
                },
                'messages as waiting_messages_count' => function($query) use($prospect) {
                    $query
                        ->where('prospect_id', $prospect->id)
                        ->whereHas('users', function($query) {
                            $query
                                ->where('id', auth()->id())
                                ->whereNull('user_message.archived_at');
                        });
                },
            ])
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $messages = Message::where('prospect_id', $prospect->id)
            ->whereIn('thread_id', $threads->pluck('id')->toArray())
            ->whereHas('users', function($query) {
                $query->where('id', auth()->id());
            })
            ->with([
                'creator:id,name',
                'users' => function($query) {
                    $query->where('id', auth()->id())->select('id', 'name');
                },
            ])
            ->select('id', 'body', 'prospect_id', 'thread_id', 'created_at', 'creator_id')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $prospect->load([
            'users' => function($query) {
                $query->where('id', auth()->id())->select('id', 'name');
            },
            'creator:id,name',
        ]);

        return [
            'number' => $number,
            'prospect' => $prospect,
            'threads' => $threads,
            'messages' => $messages,
        ];
    }

    /**
     * Fetch recent CloudTalk call history for the authenticated agent.
     */
    public function history(Request $request, Project $project, CloudTalk $cloudTalk)
    {
        $this->validate($request, [
            'number' => 'nullable|string',
            'call_id' => 'nullable|string',
            'line_id' => 'nullable|integer',
            'started_at' => 'nullable|string',
            'ended_at' => 'nullable|string',
            'direction' => 'nullable|string',
        ]);

        if (!$request->filled('number') && !$request->filled('call_id')) {
            return response()->json([
                'message' => 'Numero ou identifiant CloudTalk requis.',
            ], 422);
        }

        $line = $this->resolveLine($request, $project);

        if (!$line) {
            return response()->json([
                'message' => 'Aucune ligne CloudTalk affectee a votre utilisateur.',
            ], 404);
        }

        try {
            $filters = $this->historyFilters($request, $cloudTalk, $line);
            $history = $cloudTalk->callHistory($line, $filters);
            $call = $this->bestMatchingHistory($history, $request);

            if (!$call) {
                $history = $this->matchingHistory(
                    $this->broadCloudTalkHistory($request, $cloudTalk, $line),
                    $request
                );
                $call = $this->bestMatchingHistory($history, $request);
            }
        } catch (RuntimeException $e) {
            return $this->runtimeError($e);
        }

        return [
            'number' => $request->input('number'),
            'call' => $call,
            'history' => $history,
        ];
    }

    protected function resolveLine(Request $request, Project $project): ?Line
    {
        $query = $project
            ->lines()
            ->where('operator', 'cloudtalk')
            ->where('user_id', auth()->id());

        if ($request->filled('line_id')) {
            $query->where('id', $request->input('line_id'));
        }

        return $query->first();
    }

    protected function updateInteractionFromCallResponse(
        Request $request,
        Project $project,
        string $number,
        ?array $responseData,
        ?string $callId
    ): ?Interaction {
        if (!$request->filled('interaction_id')) {
            return null;
        }

        $interaction = Interaction::query()
            ->where('id', $request->input('interaction_id'))
            ->where('source', 'cloudtalk')
            ->where('creator_id', auth()->id())
            ->whereHas('prospect', function($query) use($project) {
                $query->where('project_id', $project->id);
            })
            ->first();

        if (!$interaction) {
            return null;
        }

        $data = is_array($interaction->data) ? $interaction->data : [];
        $data['cloudtalk'] = $responseData;

        if ($callId) {
            $data['id'] = $callId;
            $data['call_id'] = $callId;
        }

        $interaction->update([
            'number' => $number,
            'status' => 'initiated',
            'data' => $data,
        ]);

        return $interaction->fresh()->load('creator:id,name');
    }

    protected function historyFilters(Request $request, CloudTalk $cloudTalk, Line $line): array
    {
        $callId = $request->input('call_id');

        if ($callId && preg_match('/^\d+$/', (string) $callId)) {
            return [
                'call_id' => $callId,
                'limit' => 1,
                'page' => 1,
            ];
        }

        $endedAt = $this->cloudTalkDate($request->input('ended_at'), now()->addMinutes(5));
        $startedAt = $this->cloudTalkDate($request->input('started_at'), now()->subHours(6));

        return [
            'public_external' => $cloudTalk->normalizePublicNumber($request->input('number')),
            'user_id' => data_get($line->config, 'agent_id'),
            'type' => $this->cloudTalkDirection($request->input('direction')),
            'date_from' => Carbon::parse($startedAt)->subMinutes(10)->toDateTimeString(),
            'date_to' => Carbon::parse($endedAt)->addMinutes(10)->toDateTimeString(),
            'limit' => 20,
            'page' => 1,
        ];
    }

    protected function bestMatchingHistory(array $history, Request $request): ?array
    {
        return collect($this->matchingHistory($history, $request))
            ->sortByDesc(fn ($call) => $call['ended_at'] ?: $call['started_at'] ?: '')
            ->first();
    }

    protected function matchingHistory(array $history, Request $request): array
    {
        $callId = $request->input('call_id');
        $number = $this->normalizePhone($request->input('number'));
        $direction = $this->cloudTalkDirection($request->input('direction'));

        return collect($history)
            ->filter(function($call) use($callId, $number, $direction) {
                if ($callId && preg_match('/^\d+$/', (string) $callId)) {
                    return (string) $call['id'] === (string) $callId;
                }

                if ($callId && $this->callUuidMatches($call, (string) $callId)) {
                    return true;
                }

                if ($callId && !$number) {
                    return false;
                }

                if ($direction && !$this->directionsMatch($call['type'] ?? null, $direction)) {
                    return false;
                }

                return !$number || $this->phonesMatch($call['number'] ?? null, $number);
            })
            ->values()
            ->all();
    }

    protected function broadCloudTalkHistory(Request $request, CloudTalk $cloudTalk, Line $line): array
    {
        $history = [];

        foreach (range(1, 3) as $page) {
            $pageHistory = $cloudTalk->callHistory($line, array_merge(
                $this->broadHistoryFilters($request),
                ['page' => $page]
            ));

            if (!$pageHistory) {
                break;
            }

            $history = array_merge($history, $pageHistory);

            if (count($pageHistory) < 100) {
                break;
            }
        }

        return $history;
    }

    protected function broadHistoryFilters(Request $request): array
    {
        $endedAt = $this->cloudTalkDate($request->input('ended_at'), now()->addDay());
        $startedAt = $this->cloudTalkDate($request->input('started_at'), now()->subDay());

        return [
            'date_from' => Carbon::parse($startedAt)->subDay()->toDateTimeString(),
            'date_to' => Carbon::parse($endedAt)->addDay()->toDateTimeString(),
            'limit' => 100,
        ];
    }

    protected function callUuidMatches(array $call, string $callId): bool
    {
        foreach ([
            'uuid',
            'raw.Cdr.uuid',
            'raw.Cdr.call_uuid',
            'raw.Call.uuid',
            'raw.Call.call_uuid',
            'raw.uuid',
            'raw.call_uuid',
        ] as $path) {
            $value = data_get($call, $path);

            if ($value && (string) $value === $callId) {
                return true;
            }
        }

        return false;
    }

    protected function directionsMatch(?string $callDirection, string $requestDirection): bool
    {
        if (!$callDirection) {
            return true;
        }

        return $this->directionFamily($callDirection) === $this->directionFamily($requestDirection);
    }

    protected function directionFamily(string $direction): string
    {
        return match ($direction) {
            'inbound', 'incoming' => 'incoming',
            'outbound', 'outgoing' => 'outgoing',
            default => $direction,
        };
    }

    protected function cloudTalkDirection(?string $direction): ?string
    {
        return match ($direction) {
            'outbound', 'outgoing' => 'outgoing',
            'inbound', 'incoming' => 'incoming',
            'internal' => 'internal',
            default => null,
        };
    }

    protected function cloudTalkDate(?string $value, $fallback): string
    {
        try {
            return Carbon::parse($value ?: $fallback)->toDateTimeString();
        } catch (\Throwable $e) {
            return Carbon::parse($fallback)->toDateTimeString();
        }
    }

    protected function authorizeCloudTalkConfig(Project $project): void
    {
        abort_unless(
            auth()->user()->can('projectLineAdd', $project) ||
                auth()->user()->can('projectLineUpdate', $project),
            404
        );
    }

    protected function credentials(Request $request): array
    {
        $this->validate($request, [
            'api_key_id' => 'required|string',
            'api_key_secret' => 'required|string',
        ]);

        return $request->only('api_key_id', 'api_key_secret');
    }

    protected function runtimeError(RuntimeException $e)
    {
        $status = $this->httpStatus($e->getCode());

        return response()->json([
            'message' => $e->getMessage(),
            'status' => $e->getCode(),
        ], $status);
    }

    protected function httpStatus(int $status): int
    {
        if ($status === 401) {
            return 422;
        }

        return in_array($status, [403, 404, 406, 409, 422, 500, 503])
            ? $status
            : 500;
    }

    protected function findProspectByPhone(Project $project, string $normalized): ?Prospect
    {
        $suffix = substr($normalized, -8);
        $phoneExpression = $this->normalizedPhoneExpression('phone_number');
        $mobileExpression = $this->normalizedPhoneExpression('mobile_phone_number');

        return $project
            ->prospects()
            ->select(
                'id',
                'project_id',
                'creator_id',
                'first_name',
                'last_name',
                'company_name',
                'email',
                'phone_number',
                'mobile_phone_number'
            )
            ->where(function($query) use($suffix, $phoneExpression, $mobileExpression) {
                $query
                    ->whereRaw("{$phoneExpression} LIKE ?", ["%{$suffix}"])
                    ->orWhereRaw("{$mobileExpression} LIKE ?", ["%{$suffix}"]);
            })
            ->limit(20)
            ->get()
            ->first(function($prospect) use($normalized) {
                return $this->phonesMatch($prospect->phone_number, $normalized) ||
                    $this->phonesMatch($prospect->mobile_phone_number, $normalized);
            });
    }

    protected function normalizePhone(?string $number): string
    {
        return preg_replace('/\D+/', '', (string) $number);
    }

    protected function phonesMatch(?string $phoneNumber, string $normalized): bool
    {
        $phoneNumber = $this->normalizePhone($phoneNumber);

        if (!$phoneNumber || !$normalized) {
            return false;
        }

        if ($phoneNumber === $normalized) {
            return true;
        }

        $length = min(strlen($phoneNumber), strlen($normalized), 9);

        return $length >= 6 &&
            substr($phoneNumber, -$length) === substr($normalized, -$length);
    }

    protected function normalizedPhoneExpression(string $column): string
    {
        $expression = "`{$column}`";

        foreach ([' ', '.', '-', '(', ')', '+', '/'] as $character) {
            $expression = "REPLACE({$expression}, '{$character}', '')";
        }

        return $expression;
    }
}
