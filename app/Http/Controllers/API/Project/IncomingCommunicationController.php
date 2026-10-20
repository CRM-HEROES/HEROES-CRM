<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\IncomingCommunications;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IncomingCommunicationController extends Controller
{
    public function count(Request $request, Project $project, IncomingCommunications $communications)
    {
        abort_unless($request->user(), 401);

        return response()->json($communications->counts($project, $request->user()->id));
    }

    public function read(Request $request, Project $project, IncomingCommunications $communications)
    {
        abort_unless($request->user(), 401);
        $values = $request->validate([
            'prospect_id' => ['required', 'integer'],
            'type' => ['required', Rule::in(array_keys(IncomingCommunications::TYPES))],
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['required', 'integer', 'min:1', 'distinct'],
        ]);

        $marked = $communications->markRead($project, $request->user()->id, $values['prospect_id'], $values['type'], $values['ids']);

        return response()->json(['marked' => $marked] + $communications->counts($project, $request->user()->id));
    }
}
