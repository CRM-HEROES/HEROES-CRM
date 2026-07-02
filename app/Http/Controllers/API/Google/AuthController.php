<?php

namespace App\Http\Controllers\API\Google;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function login(Request $request)
    {
		$google_redirect_url = route('api.google.auth.login');
        
        $client = new \Google_Client();
        $client->setClientId(config('google.client_id'));
        $client->setClientSecret(config('google.client_secret'));

        $client->setRedirectUri($google_redirect_url);
        $client->setScopes([
            'https://www.googleapis.com/auth/plus.me',
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile',
        ]);
        
        $google_oauthV2 = new \Google_Service_Oauth2($client);

        $client->setAccessToken($request->input("access_token"));

        if ($client->getAccessToken()) {
            $guser = $google_oauthV2->userinfo->get();
            $request->session()->put('name', $guser['name']);

            $user = User::where('email', $guser['email'])->first();

            // if user does not exist yet
            // create new user
            if (!$user) {
                $user = User::create([
                    'name' => $guser->name,
                    'email' => $guser->email,
                    // 'google_id'=> $guser->id
                ]);
            }
            
            $token = $user->createToken('HeroesCRM')->plainTextToken;
            \Auth::login($user);
            
            return $token;
        }
    }
}
