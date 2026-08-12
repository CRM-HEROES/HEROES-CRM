<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\GeminiLiveService;
use Illuminate\Http\Request;

class GeminiLiveController extends Controller
{
    public function token(Request $request, GeminiLiveService $service)
    {
        return response()->json($service->createEphemeralToken(), 200);
    }
}
