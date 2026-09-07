<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Line;
use App\Models\Project;
use Illuminate\Http\Request;

class LineController extends Controller
{
    /**
     * Configuration fields required per telephony operator.
     *
     * @var array<string, array<int, string>>
     */
    protected $operatorConfigFields = [
        'kavkom' => ['api_token', 'domain_uuid', 'phone_number', 'extension'],
        'ringover' => ['api_token'],
        'twilio' => ['account_sid', 'auth_token', 'api_key_sid', 'api_key_secret', 'twiml_app_sid', 'caller_id_number'],
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        return $project
            ->lines()
            ->select('id', 'project_id', 'name', 'operator')
            ->orderBy('name')
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('projectLineAdd', $project), 404);

        $this->validate($request, $this->rules($request->input('operator')));

        return $project
            ->lines()
            ->create(array_merge($request->only(
                'name',
                'operator',
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

        $line->update($request->only(
            'name',
            'operator',
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
            'operator' => 'required|string|in:' . implode(',', array_keys($this->operatorConfigFields)),
            'config' => 'required|array',
        ];

        foreach ($this->operatorConfigFields[$operator] ?? [] as $field) {
            $rules["config.{$field}"] = 'required|string';
        }

        return $rules;
    }
}
