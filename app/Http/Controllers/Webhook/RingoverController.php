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
class RingoverController extends Controller
{
    
    /**
     * Display a listing of the resource.
     */
    public function store(Request $request)
    {
        // abort_unless($request->ip == "", 404);

        $data = $request->post();

        if ($data['resource'] == "call") {
            if ($data['event'] == "hangup") {
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
        abort_unless(isset($data['call_id']), 400);

    	// Select corresponding Aircall call from database
    	$interaction = Interaction
            ::where('source', "ringover")
            ->where('data->id', $data['call_id'])
            ->first();

        abort_unless($interaction, 404);

        $interaction->update([
            'started_at' => \Carbon\Carbon::createFromTimestamp($data['start_time']),
            'ended_at' => \Carbon\Carbon::createFromTimestamp($data['hangup_time'])
        ]);

        $this->downloadAudio($interaction, $data);
    }

    /**
     * 
     */
    protected function downloadAudio($interaction, &$data)
    {
        if (!$data['record']) {
            return;
        }

        $recording = $data['record'];
        $name = Str::random(30) . '.' . pathinfo($recording)['extension'];
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
