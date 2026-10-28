<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Monitoring\ServerMonitoring;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServerMonitoringController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        abort_unless($request->user()?->is_super_admin, 403);

        return response()->json(app(ServerMonitoring::class)->snapshot())
            ->header('Cache-Control', 'no-store, private');
    }
}
