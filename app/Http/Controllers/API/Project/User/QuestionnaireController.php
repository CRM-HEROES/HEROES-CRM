<?php

namespace App\Http\Controllers\API\Project\User;

use App\Http\Controllers\Controller;
use App\Models\Questionnaire;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionnaireController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, User $user)
    {
        abort_unless(auth()->user()->can('userQuestionnaire', $project), 404);

        return $user
            ->questionnaires()
            ->select('id', 'name')
            ->where('project_id', $project->id)
            ->get();
    }

    /**
     * Display associated users.
     */
    public function users(Project $project, Questionnaire $questionnaire)
    {
        abort_unless(auth()->user()->can('userQuestionnaire', $project), 404);
        abort_unless($project->id == $questionnaire->project_id, 404);

        return $questionnaire
            ->users()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, User $user, Questionnaire $questionnaire)
    {
        abort_unless(auth()->user()->can('userQuestionnaire', $project), 404);
        abort_unless($project->id == $questionnaire->project_id, 404);

        $user
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
    public function destroy(Project $project, User $user, Questionnaire $questionnaire)
    {
        abort_unless(auth()->user()->can('userQuestionnaire', $project), 404);
        abort_unless($project->id == $questionnaire->project_id, 404);

        $user->questionnaires()->detach($questionnaire->id);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('userQuestionnaire', $project), 404);
        
        $this->validate($request, [
            'questionnaires' => 'required',
            'users' => 'required',
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
            
        // Only users
        // associated to the current project
        $users = $project
            ->users()
            ->whereIn('id', $request->input('users'))
            ->get(['id'])
            ->map(function($user) {
                return $user->id;
            })
            ->toArray();
            
        switch ($request->input('action')) {
            case "attach":
                $this->bulkActionAttach($users, $questionnaires);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($users, $questionnaires);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach questionnaires
     */
    protected function bulkActionAttach(&$users, &$questionnaires) {
        // Remove previous data
        $this->bulkActionDetach($users, $questionnaires);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($questionnaire) use($users, $now) {
            return array_map(function($user) use($questionnaire, $now) {
                return [
                    'questionnaire_id' => $questionnaire,
                    'user_id' => $user,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $users);
        }, $questionnaires);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('user_questionnaire')->insert($data);
    }

    /**
     * Bulk detach questionnaires
     */
    protected function bulkActionDetach(&$users, &$questionnaires) {
        DB::table('user_questionnaire')
            ->whereIn('questionnaire_id', $questionnaires)
            ->whereIn('user_id', $users)
            ->delete();
    }
}
