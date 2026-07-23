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

        $config = $this->getUserKavkomConfig($request);

        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration Kavkom manquante : renseignez le jeton API et le domain_uuid dans les paramètres.',
            ], 200);
        }

        $extension = $service->resolveExtension($config['api_token'], $config['domain_uuid']);

        if (!$extension['success']) {
            return response()->json($extension, 200);
        }

        $result = $service->originateCall(
            $config['api_token'],
            $config['domain_uuid'],
            $extension['extension'],
            $data['destination']
        );

        return response()->json($result, 200);
    }

    /**
     * SIP credentials (extension, password, user_context) needed by the
     * browser to register a WebRTC softphone. The API token itself is
     * never sent to the browser, only these derived, extension-scoped
     * credentials.
     */
    public function credentials(Request $request, KavkomService $service)
    {
        $config = $this->getUserKavkomConfig($request);

        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration Kavkom manquante : renseignez le jeton API et le domain_uuid dans les paramètres.',
            ], 200);
        }

        $result = $service->resolveExtension($config['api_token'], $config['domain_uuid']);

        return response()->json($result, 200);
    }

    protected function getUserKavkomConfig(Request $request): ?array
    {
        $setting = UserSetting::query()
            ->whereNull('project_id')
            ->where('user_id', $request->user()->id)
            ->where('key', 'kavkom')
            ->first();

        $config = $setting ? (array) $setting->value : [];

        if (empty($config['api_token']) || empty($config['domain_uuid'])) {
            return null;
        }

        return $config;
    }
}
