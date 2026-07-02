<?php
 
namespace App\Http\Requests\Validator;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FAQRCode\Google2FA;
use Illuminate\Validation\Validator;
 
/**
 * Class CustomValidator
 * @package App\Http\Requests
 */
class TwoFactorsTokenValidator extends Validator
{
    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return bool
     */
    public function validateTokenIsOk($attribute, $value, $parameters)
    {
        try {
            $user = User::findOrFail(session('two_factors.user.id'));
        } catch (\Exception $exc) {
            return false;
        }
 
        $secret = \Crypt::decrypt($user->two_factors_secret);

        $google2FA = new Google2FA();
        return $google2FA->verifyKey($secret, $value);
    }
 
    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return mixed
     */
    public function validateTokenIsValid($attribute, $value, $parameters)
    {
        $secret = \Crypt::decrypt(session('two_factors.secret'));
        
        $google2FA = new Google2FA();
        return $google2FA->verifyKey($secret, $value);
    }
}