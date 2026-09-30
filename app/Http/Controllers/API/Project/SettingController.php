<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Utils\ProjectSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    protected $allowViewForAll = ['map.color'];

    /**
     * Display the specified resource.
     */
    public function show(Project $project, $setting)
    {
        abort_unless(auth()->user()->can('', $project) || in_array($setting, $this->allowViewForAll), 404);

        return ProjectSetting::get($project, $setting, config('default-settings.' . $setting, null));
    }

    /**
     * Check if setting is set
     */
    public function check(Project $project, $setting)
    {
        // Allow for all

        return ProjectSetting::get($project, $setting, null) != null;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, $setting)
    {
        abort_unless(auth()->user()->can('', $project), 404);

        $this->validate($request, [
            'value' => 'required',
        ]);

        ProjectSetting::set($project, $setting, $request->input('value'));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, $setting)
    {
        ProjectSetting::delete($project, $setting);

        return ['message' => trans('common.success.deleted_resource')];
    }
}
