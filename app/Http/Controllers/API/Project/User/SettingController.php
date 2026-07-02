<?php

namespace App\Http\Controllers\API\Project\User;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectSetting;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Project $project, User $user, $setting)
    {
        abort_unless(auth()->user()->can('', $project) || $user->id == auth()->id(), 404);

        $userSetting = UserSetting::where([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'key' => $setting,
            ])
            ->first();

        if ($userSetting) {
            return $userSetting->value;
        }

/*
        $userSetting = ProjectSetting::where([
            'project_id' => $project->id,
            'key' => $setting,
        ])
        ->first();

        if ($userSetting) {
            return $userSetting->value;
        }
*/
        return config('default-settings.' . $setting, null);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, User $user, $setting)
    {
        abort_unless(auth()->user()->can('', $project) || $user->id == auth()->id(), 404);
        abort_unless($request->has('value'), 422);
/*
        $this->validate($request, [
            'value' => 'required',
        ]);
*/

        UserSetting::updateOrCreate([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'key' => $setting,
        ], [
            'value' => $request->input('value'),
            'creator_id' => auth()->id(),
        ]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, User $user, $setting)
    {
        abort_unless(auth()->user()->can('', $project) || $user->id == auth()->id(), 404);
        
        UserSetting
            ::where([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'key' => $setting,
            ])->delete();

        return ['message' => trans('common.success.detached_resource')];
    }
}
