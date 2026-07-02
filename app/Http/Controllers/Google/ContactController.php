<?php

namespace App\Http\Controllers\Google;

use App\Http\Controllers\Controller;
use App\Models\GoogleAccount;
use App\Services\Google\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function create(Request $request, Contact $contact)
    {
        // Redirect to the previous page
        // when redirect success
        $contact->setState(json_encode([
            'referer' => $request->headers->get('referer')
        ]));

        return redirect($contact->createAuthUrl());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Contact $contact)
    {
        $this->validate($request, [
            'code' => 'required',
            'state' => 'required'
        ]);

        $state = json_decode($request->get('state'), true);

        $accessToken = $contact->fetchAccessTokenWithAuthCode($request->get('code'));
        $contact->setAccessToken($accessToken);
        
        // Get connected user info
        $oauth2 = new \Google_Service_Oauth2($contact);
        $userInfo = $oauth2->userinfo->get();

        // update token
        $googleAccount = GoogleAccount::withTrashed()->updateOrCreate([
                // User
                'user_id' => auth()->id(),
                // Map the account's id to the `google_id`.
                'google_id' => $userInfo->id,
                // For Google Contact
                'for' => "contact",
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
