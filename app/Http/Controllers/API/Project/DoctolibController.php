<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Jobs\Doctolib\AppointmentsGet;
use App\Models\DoctolibAccount;
use App\Models\Project;
use Illuminate\Http\Request;

class DoctolibController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        return DoctolibAccount::where([
            // Project
            'project_id' => $project->id,
        ])->first();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('', $project), 404);

        $this->validate($request, [
            'name' => 'required',
            'token' => 'nullable',
        ]);

        DoctolibAccount::where('project_id', $project->id)->delete();

        $account = DoctolibAccount::withTrashed()
            ->where('project_id', $project->id)
            ->first();

        if ($account) {
            $account->update([
                'name' => $request->input('name'),
                'token' => $request->input('token'),
                'deleted_at' => null,
            ]);
        } else {
            $account = DoctolibAccount::create([
                // Project
                'project_id' => $project->id,
                'user_id' => auth()->id(),
                'name' => $request->input('name'),
                'token' => $request->input('token'),
            ]);
        }

        return $account;
    }

    /**
     * Manually trigger an appointments import for this account.
     *
     * NOT FUNCTIONAL YET: dispatches App\Jobs\Doctolib\AppointmentsGet,
     * which is a stub until Doctolib API/ICS access is configured.
     */
    public function sync(Project $project, DoctolibAccount $doctolib)
    {
        abort_unless(auth()->user()->can('', $project), 404);
        abort_unless($project->id == $doctolib->project_id, 404);

        AppointmentsGet::dispatch($doctolib, $project)->onConnection('sync');

        return ['message' => 'Doctolib sync dispatched (not implemented yet, see App\Jobs\Doctolib\AppointmentsGet)'];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, DoctolibAccount $doctolib)
    {
        abort_unless(auth()->user()->can('', $project), 404);
        abort_unless($doctolib->project_id == $project->id, 404);

        $doctolib->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
