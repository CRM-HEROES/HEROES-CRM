<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(User $user, $setting)
    {
        abort_unless(auth()->id() == $user->id || auth()->user()->is_super_admin, 404);
        
        $userSetting = UserSetting::where([
                'project_id' => null,
                'user_id' => $user->id,
                'key' => $setting,
            ])
            ->first();

        if ($userSetting) {
            return $userSetting->value;
        }

        return config('default-settings.' . $setting, null);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user, $setting)
    {
        abort_unless(auth()->id() == $user->id || auth()->user()->is_super_admin, 404);

        $this->validate($request, [
            'value' => 'required',
        ]);

        UserSetting::updateOrCreate([
            'project_id' => null,
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
    public function destroy(User $user, $setting)
    {
        abort_unless(auth()->id() == $user->id || auth()->user()->is_super_admin, 404);

        UserSetting
            ::where([
                'project_id' => null,
                'user_id' => $user->id,
                'key' => $setting,
            ])->delete();

        return ['message' => trans('common.success.detached_resource')];
    }
}
