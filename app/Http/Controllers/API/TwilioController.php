<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TwilioController extends Controller
{
    /**
     * Issues a short-lived Access Token for the browser Voice SDK. The
     * Account SID / Auth Token / API Key secret never reach the browser.
     */
    public function token(Request $request, TwilioService $service)
    {
        $result = $service->issueAccessToken('user-' . $request->user()->id);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration Twilio manquante côté serveur.',
            ], 200);
        }

        Log::channel('twilio')->info('Issued Twilio Access Token.', [
            'user_id' => $request->user()->id,
        ]);

        return response()->json(['success' => true] + $result, 200);
    }

    /**
     * Public webhook: Twilio's TwiML App calls this when the browser SDK's
     * Device.connect() places an outbound call. No CRM session here —
     * authenticated via X-Twilio-Signature instead.
     */
    public function voice(Request $request, TwilioService $service)
    {
        if (!$service->validateSignature($request)) {
            Log::channel('twilio')->warning('Rejected Twilio voice webhook: invalid signature.');

            return response(
                '<?xml version="1.0" encoding="UTF-8"?><Response><Reject/></Response>',
                403
            )->header('Content-Type', 'text/xml');
        }

        $to = $request->input('To');
        $twiml = $service->voiceTwiml($to);

        Log::channel('twilio')->info('Twilio voice webhook served TwiML.', [
            'to_suffix' => substr((string) $to, -4),
        ]);

        return response($twiml, 200)->header('Content-Type', 'text/xml');
    }
}
