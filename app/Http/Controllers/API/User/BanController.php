<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\User;

class BanController extends Controller
{
    /**
     * Update the specified resource in storage.
     */
    public function update(User $user)
    {
        abort_unless(auth()->user()->is_super_admin, 404);
        abort_unless(auth()->id() != $user->id, 404);

        $user
            ->update([
                'banned_at' => \Carbon\Carbon::now()
            ]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        abort_unless(auth()->user()->is_super_admin, 404);
        abort_unless(auth()->id() != $user->id, 404);

        $user
            ->update([
                'banned_at' => null
            ]);

        return ['message' => trans('common.success.detached_resource')];
    }
}
