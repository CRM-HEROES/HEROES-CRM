<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Interaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Webhook for Aircall service
 * Il will retrieve informations
 * about calls made via Aircall
 * shuch as call started at, ended at
 * and call recording
 */
class AircallController extends Controller
{
    
    /**
     * Display a listing of the resource.
     */
    public function store(Request $request)
    {
        // abort_unless($request->ip == "", 404);

        $data = $request->post();

        if ($data['resource'] == "call") {
            if ($data['event'] == "call.ended") {
                $this->callEnded($data['data']);
            }
        }

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * 
     */
    protected function callEnded(&$data)
    {
        abort_unless($data['id'], 400);

    	// Select corresponding Aircall call from database
    	$interaction = Interaction
            ::where('source', "aircall")
            ->where('data->id', $data['id'])
            ->first();

        if (!$interaction) {
            return;
        }

        $interaction->update([
            'started_at' => \Carbon\Carbon::createFromTimestamp($data['started_at']),
            'ended_at' => \Carbon\Carbon::createFromTimestamp($data['ended_at'])
        ]);

        $this->downloadAudio($interaction, $data);
    }

    /**
     * 
     */
    protected function downloadAudio($interaction, &$data)
    {
        if (!$data['recording']) {
            return;
        }

        $recording = $data['recording'];
        $recordingName = substr($recording, 0, strpos($recording, '?'));
        $name = Str::random(30) . '.' . pathinfo($recordingName)['extension'];
        $path = $interaction->prospect->project->slug . '/' . $name;

        // Download and Store content
        $content = file_get_contents($recording);
        $disk = Storage::disk('interactions');
        $disk->put($path, $content);

        // Update interaction
        $interaction->update([
            'path' => $path,
            'size' => $disk->size($path)
        ]);
    }
}
