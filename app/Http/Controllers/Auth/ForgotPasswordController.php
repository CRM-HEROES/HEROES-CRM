<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function __construct()
    {
        // Évite d'inonder une boîte mail (ou de sonder des adresses) depuis une même IP
        $this->middleware('throttle:6,1')->only('sendResetLinkEmail');
    }

    /**
     * Même réponse que pour un envoi réussi quand l'adresse est inconnue ou qu'une demande vient déjà d'être faite :
     * on ne révèle pas quelles adresses ont un compte.
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return $this->sendResetLinkResponse($request, Password::RESET_LINK_SENT);
    }
}
