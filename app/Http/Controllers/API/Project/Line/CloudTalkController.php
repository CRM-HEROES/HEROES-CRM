<?php

namespace App\Http\Controllers\API\Project\Line;

use App\Http\Controllers\Controller;
use App\Models\Line;
use App\Models\Project;
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
}
