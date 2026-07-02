<?php

namespace App\Http\Controllers\API\Google;

use App\Http\Controllers\Controller;
use App\Http\Requests\TwoFactorsRequest;
use App\Http\Requests\TwoFactorsValidateRequest;
use App\Models\User;
use PragmaRX\Google2FAQRCode\Google2FA;
use Illuminate\Http\Request;

class AuthenticatorController extends Controller
{
    /**
     * Enable two factors authentication.
     */
    public function enable()
    {
        $google2FA = new Google2FA();
        // Generate a secret key in Base32 format
        $secret = $google2FA->generateSecretKey();
        // $secret = strtoupper(Base32::encode(random_bytes(10)));
        $user = auth()->user();
 
        session()->forget(['two_factors.secret', 'two_factors.user.id']);
        session([
            'two_factors.secret' => \Crypt::encrypt($secret), 
            'two_factors.user.id' => auth()->id()
        ]);
 
        //generate image for QR barcode
        $QRCode = $google2FA->getQRCodeInline(
            config('app.name'),
            $user->email,
            $secret,
            200
        );
 
        return [
            'qrcode' => $QRCode,
            'secret' => $secret,
            'encryt' => session('two_factors.secret'),
            'encryt2' => \Crypt::encrypt($secret)
        ];
    }
    
    /**
     * Activate two factors authentication.
     */
    public function activate(Request $request)
    {
        auth()->user()->two_factors_check_type = $request->input('check_type', null);
        auth()->user()->two_factors_secret = session('two_factors.secret');
        auth()->user()->save();
        session()->forget(['two_factors.secret', 'two_factors.user.id']);

        return [
            "message" => "Two factors authentication successfully activated."
        ];
    }
    
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function deactivate()
    {
        $user = auth()->user();
 
        $user->two_factors_secret = null;
        $user->save();
 
        return [
            "message" => "Two factors authentication successfully deactivated."
        ];
    }
 
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function check()
    {
        $user = auth()->user();
        return $user->two_factors_secret ? 1 : 0;
    }
 
    /**
     * Validate code from two factors authentication.
     */
    public function login(TwoFactorsValidateRequest $request)
    {
        try {
            $user = User::findOrFail($request->session()->pull('two_factors.user.id'));
            $token = $user->createToken('HeroesCRM')->plainTextToken;
            \Auth::login($user);
            
            return $token;
        } catch (\Exception $e) {
            return response()->json(['message' => 'User not found'], 404);
        }
    }
}
