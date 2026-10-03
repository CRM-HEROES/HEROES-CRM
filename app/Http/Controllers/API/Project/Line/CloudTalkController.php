<?php

namespace App\Http\Controllers\API\Project\Line;

use App\Http\Controllers\Controller;
use App\Models\Line;
use App\Models\Message;
use App\Models\Project;
use App\Models\Prospect;
use App\Services\CloudTalk;
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

        return [
            'message' => 'Appel CloudTalk lance.',
            'number' => $number,
            'responseData' => data_get($response, 'responseData'),
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
