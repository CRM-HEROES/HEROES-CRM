<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Line;
use App\Models\Project;
use App\Services\CloudTalk;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class LineController extends Controller
{
    /**
     * Configuration fields required per telephony operator.
     *
     * @var array<string, array<int, string>>
     */
    protected $operatorConfigFields = [
        'kavkom' => ['api_token', 'domain_uuid', 'phone_number', 'extension'],
        'cloudtalk' => ['api_key_id', 'api_key_secret', 'agent_id'],
        'ringover' => ['api_token'],
        'twilio' => [
            'account_sid',
            'auth_token',
            'api_key_sid',
            'api_key_secret',
            'twiml_app_sid',
            'caller_id_number',
        ],
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        return $project
            ->lines()
            ->select('id', 'project_id', 'name', 'operator', 'user_id', 'config')
            ->orderBy('name')
            ->get()
            ->each(function (Line $line) {
                $line->setAttribute('numero', $line->numero);
                $line->makeHidden('config');
            });
    }

    /**
     * Users that do not already have a config for the selected operator.
     */
    public function availableUsers(Request $request, Project $project)
    {
        abort_unless(
            auth()->user()->can('projectLineAdd', $project) ||
                auth()->user()->can('projectLineUpdate', $project),
            404
        );

        $this->validate($request, [
            'operator' => 'required|string|in:' .
                implode(',', array_keys($this->operatorConfigFields)),
            'exclude_line_id' => 'nullable|integer',
        ]);

        $excludeLineId = $request->input('exclude_line_id');

        if ($excludeLineId) {
            abort_unless(
                $project->lines()->where('id', $excludeLineId)->exists(),
                404
            );
        }

        $assignedUserIds = $project
            ->lines()
            ->where('operator', $request->input('operator'))
            ->when($excludeLineId, function ($query) use ($excludeLineId) {
                $query->where('id', '!=', $excludeLineId);
            })
            ->whereNotNull('user_id')
            ->pluck('user_id');

        return $project
            ->users()
            ->select(
                'users.id',
                'users.name',
                'users.last_name',
                'users.email',
                'users.role',
                'users.creator_id'
            )
            ->forCurrentUser()
            ->whereNotIn('users.id', $assignedUserIds)
            ->orderBy('users.name')
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('projectLineAdd', $project), 404);

        $this->validate($request, $this->rules($request->input('operator')));
        $this->validateProjectUser($project, (int) $request->input('user_id'));
        $this->validateUniqueAgentOperatorConfig(
            $project,
            $request->input('operator'),
            (int) $request->input('user_id')
        );
        $this->validateOperatorConfig(
            $request->input('operator'),
            $request->input('config', [])
        );

        return $project
            ->lines()
            ->create(array_merge($request->only(
                'name',
                'operator',
                'user_id',
                'config'
            ), [
                'creator_id' => auth()->id(),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Line $line)
    {
        abort_unless($project->id == $line->project_id, 404);

        return $line;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Line $line)
    {
        abort_unless(auth()->user()->can('projectLineUpdate', $project), 404);
        abort_unless($project->id == $line->project_id, 404);

        $this->validate($request, $this->rules($request->input('operator')));
        $this->validateProjectUser($project, (int) $request->input('user_id'));
        $this->validateUniqueAgentOperatorConfig(
            $project,
            $request->input('operator'),
            (int) $request->input('user_id'),
            $line
        );
        $this->validateOperatorConfig(
            $request->input('operator'),
            $request->input('config', [])
        );

        $line->update($request->only(
            'name',
            'operator',
            'user_id',
            'config'
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Line $line)
    {
        abort_unless(auth()->user()->can('projectLineDelete', $project), 404);
        abort_unless($project->id == $line->project_id, 404);

        $line->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }

    /**
     * Validation rules, including the operator-specific
     * required configuration fields.
     */
    protected function rules($operator)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'operator' => 'required|string|in:' .
                implode(',', array_keys($this->operatorConfigFields)),
            'user_id' => 'required|exists:users,id',
            'config' => 'required|array',
        ];

        foreach ($this->operatorConfigFields[$operator] ?? [] as $field) {
            $rules["config.{$field}"] = 'required|string';
        }

        return $rules;
    }

    protected function validateProjectUser(Project $project, int $userId): void
    {
        if ($project->users()->where('users.id', $userId)->exists()) {
            return;
        }

        throw ValidationException::withMessages([
            'user_id' => "L'agent sélectionné n'appartient pas au projet.",
        ]);
    }

    protected function validateUniqueAgentOperatorConfig(
        Project $project,
        string $operator,
        int $userId,
        ?Line $line = null
    ): void {
        $exists = $project
            ->lines()
            ->where('operator', $operator)
            ->where('user_id', $userId)
            ->when($line, function ($query) use ($line) {
                $query->where('id', '!=', $line->id);
            })
            ->exists();

        if (!$exists) {
            return;
        }

        throw ValidationException::withMessages([
            'user_id' => 'Cet agent a déjà une configuration pour cet opérateur.',
        ]);
    }

    protected function validateOperatorConfig(string $operator, array $config): void
    {
        if ($operator !== 'cloudtalk') {
            return;
        }

        try {
            $agents = app(CloudTalk::class)->agents($config);
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages([
                'config.api_key_id' => $e->getMessage(),
            ]);
        }

        $agentExists = collect($agents)->contains(
            fn ($agent) => (string) $agent['id'] === (string) ($config['agent_id'] ?? '')
        );

        if (!$agentExists) {
            throw ValidationException::withMessages([
                'config.agent_id' => 'Agent CloudTalk introuvable pour ces identifiants.',
            ]);
        }
    }
}
