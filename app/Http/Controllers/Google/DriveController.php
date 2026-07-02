<?php

namespace App\Http\Controllers\Google;

use App\Http\Controllers\Controller;
use App\Models\GoogleAccount;
use App\Models\Project;
use App\Services\Google\Drive as GoogleDrive;
use Illuminate\Http\Request;

class DriveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function create(Request $request, GoogleDrive $drive)
    {
        $this->validate($request, [
            'project' => 'required',
        ]);

        abort_unless($project = Project::where('slug', $request->input('project'))->first(), 404);
        // abort_unless($project->users()->find(auth()->id()), 404);

        // Redirect to the previous page
        // when redirect success
        $drive->setState(json_encode([
            'referer' => $request->headers->get('referer'),
            'project' => $project->slug,
        ]));

        return redirect($drive->createAuthUrl());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, GoogleDrive $drive)
    {
        $this->validate($request, [
            'code' => 'required',
            'state' => 'required'
        ]);

        $state = json_decode($request->get('state'), true);

        abort_unless($project = Project::where('slug', $state['project'])->first(), 404);
        // abort_unless($project->users()->find(auth()->id()), 404);
        // To do: Only project admin here ...

        $accessToken = $drive->fetchAccessTokenWithAuthCode($request->get('code'));
        $drive->setAccessToken($accessToken);
        
        // Get connected user info
        $oauth2 = new \Google_Service_Oauth2($drive);
        $userInfo = $oauth2->userinfo->get();

        // update token
        $googleAccount = GoogleAccount::withTrashed()->updateOrCreate([
                // User
                'project_id' => $project->id,
                // Map the account's id to the `google_id`.
                'google_id' => $userInfo->id,
                // For Google Drive
                'for' => "drive",
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
