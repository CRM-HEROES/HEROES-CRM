<?php

namespace App\Http\Controllers\API\Project\Google;

use App\Http\Controllers\Controller;
use App\Models\GoogleAccount;
use App\Models\Project;

class DriveController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function index(Project $project)
    {
        return GoogleAccount::where([
            // Project
            'project_id' => $project->id,
            // For Google Drive
            'for' => "drive",
        ])->first();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, GoogleAccount $account)
    {
        abort_unless($account->project_id == $project->id, 404);
        abort_unless($account->for == "drive", 404);

        $account->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
