<?php

namespace App\Http\Controllers\API\Project\Role;

use App\Http\Controllers\Controller;
use App\Models\Questionnaire;
use App\Models\Project;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionnaireController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Role $role)
    {
        return $role
            ->questionnaires()
            ->select('id', 'name')
            ->where('project_id', $project->id)
            ->get();
    }

    /**
     * Display associated roles.
     */
    public function roles(Project $project, Questionnaire $questionnaire)
    {
        abort_unless($project->id == $questionnaire->project_id, 404);

        return $questionnaire
            ->roles()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Role $role, Questionnaire $questionnaire)
    {
        abort_unless($project->id == $questionnaire->project_id, 404);

        $role
            ->questionnaires()
            ->withPivot('creator_id', 'created_at', 'updated_at')
            ->syncWithoutDetaching([$questionnaire->id => [
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Role $role, Questionnaire $questionnaire)
    {
        abort_unless($project->id == $questionnaire->project_id, 404);

        $role->questionnaires()->detach($questionnaire->id);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        $this->validate($request, [
            'questionnaires' => 'required',
            'roles' => 'required',
            'action' => 'required',
        ]);

        // Only questionnaires
        // associated to the current project
        $questionnaires = $project
            ->questionnaires()
            ->whereIn('id', $request->input('questionnaires'))
            ->get(['id'])
            ->map(function($questionnaire) {
                return $questionnaire->id;
            })
            ->toArray();
            
        // Only roles
        // associated to the current project
        $roles = $project
            ->roles()
            ->whereIn('id', $request->input('roles'))
            ->get(['id'])
            ->map(function($role) {
                return $role->id;
            })
            ->toArray();
            
        switch ($request->input('action')) {
            case "attach":
                $this->bulkActionAttach($roles, $questionnaires);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($roles, $questionnaires);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach questionnaires
     */
    protected function bulkActionAttach(&$roles, &$questionnaires) {
        // Remove previous data
        $this->bulkActionDetach($roles, $questionnaires);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($questionnaire) use($roles, $now) {
            return array_map(function($role) use($questionnaire, $now) {
                return [
                    'questionnaire_id' => $questionnaire,
                    'role_id' => $role,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $roles);
        }, $questionnaires);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('role_questionnaire')->insert($data);
    }

    /**
     * Bulk detach questionnaires
     */
    protected function bulkActionDetach(&$roles, &$questionnaires) {
        DB::table('role_questionnaire')
            ->whereIn('questionnaire_id', $questionnaires)
            ->whereIn('role_id', $roles)
            ->delete();
    }
}
