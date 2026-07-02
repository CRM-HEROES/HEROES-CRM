<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Jobs\Pipedrive\DealsGet;
use App\Jobs\Pipedrive\PersonsGet;
use App\Jobs\Pipedrive\WebhookAdd;
use App\Models\PipedriveAccount;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class PipedriveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        return PipedriveAccount::where([
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
            'token' => 'required',
        ]);

        PipedriveAccount::where('project_id', $project->id)->delete();

        $account = PipedriveAccount::withTrashed()
            ->where('project_id', $project->id)
            ->where('token', $request->input('token'))
            ->first();

        if ($account) {
            $account->update([
                'name' => $request->input('name'),
                'deleted_at' => null
            ]);
        } else {
            $account = PipedriveAccount::create([
                // Project
                'project_id' => $project->id,
                // Token
                'token' => $request->input('token'),
                'name' => $request->input('name'),
                'password' => Str::random(20),
            ]);
        }

        return $account;
    }

    /**
     * 
     */
    public function person(Project $project, PipedriveAccount $pipedrive)
    {
        abort_unless(auth()->user()->can('', $project), 404);
        abort_unless($project->id == $pipedrive->project_id, 404);

        PersonsGet::dispatch($pipedrive, $project)->onConnection('sync'); //->onQueue('imports');
        DealsGet::dispatch($pipedrive, $project)->onConnection('sync'); //->onQueue('imports');

        return ['message' => 'Retrieving persons from Pipedrive finished'];
    }

    /**
     * 
     */
    public function webhook(Project $project, PipedriveAccount $pipedrive)
    {
        abort_unless(auth()->user()->can('', $project), 404);
        abort_unless($project->id == $pipedrive->project_id, 404);

        // Added person webhook
        WebhookAdd::dispatch(
            $pipedrive, 
            env("APP_NAME") . ' - ' . $project->name . ' - Person added',
            route("webhook.project.pipedrive.person.store", ['project' => $project->slug]),
            'added',
            'person'
        )->onConnection('sync'); // ->onQueue('imports');
        
        // Updated person webhook
        WebhookAdd::dispatch(
            $pipedrive, 
            env("APP_NAME") . ' - ' . $project->name . ' - Person updated',
            route("webhook.project.pipedrive.person.update", ['project' => $project->slug]),
            'updated',
            'person'
        )->onConnection('sync'); // ->onQueue('imports');
        
        // Added deal webhook
        WebhookAdd::dispatch(
            $pipedrive, 
            env("APP_NAME") . ' - ' . $project->name . ' - Deal added',
            route("webhook.project.pipedrive.deal.store", ['project' => $project->slug]),
            'added',
            'deal'
        )->onConnection('sync'); // ->onQueue('imports');
        
        // Updated deal webhook
        WebhookAdd::dispatch(
            $pipedrive, 
            env("APP_NAME") . ' - ' . $project->name . ' - Deal updated',
            route("webhook.project.pipedrive.deal.update", ['project' => $project->slug]),
            'updated',
            'deal'
        )->onConnection('sync'); // ->onQueue('imports');
        
        return ['message' => 'Webhooks created successfully'];
    }

    /**
     * 
     */
    public function import(Project $project, PipedriveAccount $pipedrive)
    {
        abort_unless(auth()->user()->can('', $project), 404);
        abort_unless($project->id == $pipedrive->project_id, 404);

        Artisan::call('app:pipedrive-add-persons', ['--account' => $pipedrive->id]);
        
        return ['message' => 'Importing prospects into pipedrive account ...'];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, PipedriveAccount $pipedrive)
    {
        abort_unless(auth()->user()->can('', $project), 404);
        abort_unless($pipedrive->project_id == $project->id, 404);

        $pipedrive->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
