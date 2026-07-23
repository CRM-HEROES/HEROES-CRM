<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use App\Services\KavkomService;
use Illuminate\Http\Request;

class KavkomController extends Controller
{
    public function test(Request $request, KavkomService $service)
    {
        $data = $request->validate([
            'api_token' => ['required', 'string'],
            'domain_uuid' => ['required', 'string'],
        ]);

        $result = $service->testConnection($data['api_token'], $data['domain_uuid']);

        return response()->json($result, 200);
    }

    public function call(Request $request, KavkomService $service)
    {
        $data = $request->validate([
            'destination' => ['required', 'string'],
        ]);

        $setting = UserSetting::query()
            ->whereNull('project_id')
            ->where('user_id', $request->user()->id)
            ->where('key', 'kavkom')
            ->first();

        $config = $setting ? (array) $setting->value : [];

        if (empty($config['api_token']) || empty($config['domain_uuid'])) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration Kavkom manquante : renseignez le jeton API et le domain_uuid dans les paramètres.',
            ], 200);
        }

        $src = $request->user()->mobile_phone_number ?: $request->user()->phone_number;

        $result = $service->originateCall(
            $config['api_token'],
            $config['domain_uuid'],
            (string) $src,
            $data['destination']
        );

        return response()->json($result, 200);
    }
}
