<?php

namespace App\Http\Controllers\API\Google;

use App\Http\Controllers\Controller;
use App\Models\GoogleAccount;

class ContactController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function index()
    {
        return GoogleAccount::where([
            // User
            'user_id' => auth()->id(),
            // For Google Contact
            'for' => "contact",
        ])->first();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GoogleAccount $account)
    {
        abort_unless($account->user_id == auth()->id(), 404);
        abort_unless($account->for == "contact", 404);

        $account->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
