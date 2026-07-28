<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'value.phone_number' => $setting === 'kavkom'
                ? ['required', 'string', 'max:255']
                : ['nullable'],
        ]);

        DB::transaction(function () use ($request, $user, $setting) {
            UserSetting::updateOrCreate([
                'project_id' => null,
                'user_id' => $user->id,
                'key' => $setting,
            ], [
                'value' => $request->input('value'),
                'creator_id' => auth()->id(),
            ]);

            if ($setting === 'kavkom') {
                $phoneNumber = trim($request->input('value.phone_number'));

                // Kavkom uses one caller number; keep the two CRM fields in sync.
                $user->update([
                    'phone_number' => $phoneNumber,
                    'mobile_phone_number' => $phoneNumber,
                ]);
            }
        });

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
