<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Services\ClaudeApiKeyValidator;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;

class ClaudeSettingController extends Controller
{
    public function show(Request $request, ClaudeApiKeyValidator $validator)
    {
        $user = $request->user();
        $configured = filled($user->getRawOriginal('claude_api_key'));
        $validation = ['valid' => null, 'status' => 'not_configured'];
        if ($configured) {
            try {
                $validation = $validator->check($user->claude_api_key);
            } catch (DecryptException $e) {
                $validation = ['valid' => null, 'status' => 'verification_unavailable'];
            }
        }

        return response()->json(['configured' => $configured] + $validation)
            ->header('Cache-Control', 'private, no-store');
    }

    public function update(Request $request, ClaudeApiKeyValidator $validator)
    {
        if (is_string($request->input('api_key'))) {
            $request->merge(['api_key' => trim($request->input('api_key'))]);
        }

        $values = $request->validate([
            'api_key' => 'required|string|max:4096',
        ]);

        $user = $request->user();
        $validation = $validator->check($values['api_key']);
        if ($validation['valid'] !== true) {
            return response()->json([
                'configured' => filled($user->getRawOriginal('claude_api_key')),
            ] + $validation, $validation['valid'] === false ? 422 : 503)
                ->header('Cache-Control', 'private, no-store');
        }

        $user->claude_api_key = $values['api_key'];
        $user->save();

        return response()->json(['configured' => true] + $validation)
            ->header('Cache-Control', 'private, no-store');
    }
}
