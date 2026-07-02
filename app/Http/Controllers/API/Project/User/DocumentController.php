<?php

namespace App\Http\Controllers\API\Project\User;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, User $user)
    {
        abort_unless(auth()->user()->can('userDocument', $project), 404);

        return $user
            ->documents()
            ->select('id', 'name')
            ->where('project_id', $project->id)
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, User $user, Document $document)
    {
        abort_unless(auth()->user()->can('userDocument', $project), 404);
        abort_unless($project->id == $document->project_id, 404);

        $user
            ->documents()
            ->withPivot('creator_id', 'created_at', 'updated_at')
            ->syncWithoutDetaching([$document->id => [
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, User $user, Document $document)
    {
        abort_unless(auth()->user()->can('userDocument', $project), 404);
        abort_unless($project->id == $document->project_id, 404);

        $user->documents()->detach($document->id);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('userDocument', $project), 404);
        
        $this->validate($request, [
            'documents' => 'required',
            'users' => 'required',
            'action' => 'required',
        ]);

        // Only documents
        // associated to the current project
        $documents = $project
            ->documents()
            ->whereIn('id', $request->input('documents'))
            ->get(['id'])
            ->map(function($document) {
                return $document->id;
            })
            ->toArray();
            
        // Only users
        // associated to the current project
        $users = $project
            ->users()
            ->whereIn('id', $request->input('users'))
            ->get(['id'])
            ->map(function($thread) {
                return $thread->id;
            })
            ->toArray();
            
        switch ($request->input('action')) {
            case "attach":
                $this->bulkActionAttach($users, $documents);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($users, $documents);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach documents
     */
    protected function bulkActionAttach(&$users, &$documents) {
        // Remove previous data
        $this->bulkActionDetach($users, $documents);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($document) use($users, $now) {
            return array_map(function($user) use($document, $now) {
                return [
                    'document_id' => $document,
                    'user_id' => $user,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $users);
        }, $documents);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('user_thread')->insert($data);
    }

    /**
     * Bulk detach documents
     */
    protected function bulkActionDetach(&$users, &$documents) {
        DB::table('user_document')
            ->whereIn('document_id', $documents)
            ->whereIn('user_id', $users)
            ->delete();
    }
}
