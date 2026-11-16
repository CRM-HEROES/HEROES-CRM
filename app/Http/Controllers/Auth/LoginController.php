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

    use AuthenticatesUsers {
        login as protected traitLogin;
        sendFailedLoginResponse as protected traitSendFailedLoginResponse;
    }

    public function login(Request $request)
    {
        if (\App\Services\LoginAttemptLimiter::isLocked($request->input('email'))) {
            return \App\Services\LoginAttemptLimiter::lockedResponse();
        }

        return $this->traitLogin($request);
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        if (\App\Services\LoginAttemptLimiter::recordFailure($request->input('email'))) {
            return \App\Services\LoginAttemptLimiter::lockedResponse();
        }

        return $this->traitSendFailedLoginResponse($request);
    }

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
        \App\Services\LoginAttemptLimiter::reset($user);

        if ($user->must_change_password && (
            !$user->password_temporary_expires_at ||
            $user->password_temporary_expires_at->isPast()
        )) {
            \Auth::logout();

            return response()->json([
                'code' => 'password_expired',
                'message' => 'Votre mot de passe temporaire a expiré. Veuillez contacter l’administrateur.'
            ], 403);
        }

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

        if (\App\Services\LoginLocationCheck::check($user, $request->ip())) {
            \Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return \App\Services\LoginLocationCheck::blockedResponse();
        }

        return response()->json(['message' => 'Login successful'], 200);
    }

}
