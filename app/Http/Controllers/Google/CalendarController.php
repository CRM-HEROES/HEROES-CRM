<?php

namespace App\Http\Controllers\Google;

use App\Http\Controllers\Controller;
use App\Models\GoogleAccount;
use App\Services\Google\Calendar;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function create(Request $request, Calendar $calendar)
    {
        // Redirect to the previous page
        // when redirect success
        $calendar->setState(json_encode([
            'referer' => $request->headers->get('referer')
        ]));

        return redirect($calendar->createAuthUrl());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Calendar $calendar)
    {
        $this->validate($request, [
            'code' => 'required',
            'state' => 'required'
        ]);

        $state = json_decode($request->get('state'), true);

        $accessToken = $calendar->fetchAccessTokenWithAuthCode($request->get('code'));
        $calendar->setAccessToken($accessToken);
        
        // Get connected user info
        $oauth2 = new \Google_Service_Oauth2($calendar);
        $userInfo = $oauth2->userinfo->get();

        // update token
        $googleAccount = GoogleAccount::withTrashed()->updateOrCreate([
                // User
                'user_id' => auth()->id(),
                // Map the account's id to the `google_id`.
                'google_id' => $userInfo->id,
                // For Google Calendar
                'for' => "calendar",
            ], [
                // Use the first email address as the Google account's name.
                'name' => $userInfo->email,
                
                // Last but not least, save the access token for later use.
                'token' => $accessToken,
            ]
        );
        
        // if an existing google account with the same google_id already exists
        // then restore it
        if ($googleAccount->trashed()) {
            $googleAccount->restore();
        }

        return redirect()->to($state['referer']);
    }
}
