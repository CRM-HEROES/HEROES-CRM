<?php

namespace App\Http\Controllers\API\Project\Role;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Project;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Role $role)
    {
        return $role
            ->documents()
            ->select('id', 'name')
            ->where('project_id', $project->id)
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Role $role, Document $document)
    {
        abort_unless($project->id == $document->project_id, 404);

        $role
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
    public function destroy(Project $project, Role $role, Document $document)
    {
        abort_unless($project->id == $document->project_id, 404);

        $role->documents()->detach($document->id);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        $this->validate($request, [
            'documents' => 'required',
            'roles' => 'required',
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
            
        // Only roles
        // associated to the current project
        $roles = $project
            ->roles()
            ->whereIn('id', $request->input('roles'))
            ->get(['id'])
            ->map(function($thread) {
                return $thread->id;
            })
            ->toArray();
            
        switch ($request->input('action')) {
            case "attach":
                $this->bulkActionAttach($roles, $documents);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($roles, $documents);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach documents
     */
    protected function bulkActionAttach(&$roles, &$documents) {
        // Remove previous data
        $this->bulkActionDetach($roles, $documents);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($document) use($roles, $now) {
            return array_map(function($role) use($document, $now) {
                return [
                    'document_id' => $document,
                    'role_id' => $role,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $roles);
        }, $documents);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('role_thread')->insert($data);
    }

    /**
     * Bulk detach documents
     */
    protected function bulkActionDetach(&$roles, &$documents) {
        DB::table('role_document')
            ->whereIn('document_id', $documents)
            ->whereIn('role_id', $roles)
            ->delete();
    }
}
