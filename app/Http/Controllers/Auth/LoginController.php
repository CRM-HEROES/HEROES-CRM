<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    
    /**
     * When user is authenticated
     */
    protected function authenticated(Request $request, Authenticatable $user)
    {
        if (!empty($user->two_factors_secret)) {
            $session = DB::table('tracker_sessions')
                ->where([
                    'user_id' => $user->id,
                    'client_ip' => $request->getClientIp()
                ])
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$user->two_factors_check_type || !$session) {

                \Auth::logout();
        
                $request->session()->put('two_factors.user.id', $user->id);

                return response()->json([
                    'message' => 'Two factors validation'
                ], 422);

            }
        }
     
        return response()->json(['message' => 'Login successful'], 200);
    }

}
