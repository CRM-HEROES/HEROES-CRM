<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Prospect;
use Illuminate\Http\Request;

class ProspectAiAgentController extends Controller
{
    /** Assign (or unassign) the active AI voice agent used for this prospect. */
    public function update(Request $request, Project $project, Prospect $prospect)
    {
        abort_unless((int) $prospect->project_id === (int) $project->id, 404);
        abort_unless(auth()->user()->is_super_admin || auth()->user()->can('', $project), 404);

        $data = $request->validate([
            'ai_agent_id' => ['nullable', 'integer'],
        ]);

        if (!empty($data['ai_agent_id'])) {
            $project->aiAgents()
                ->whereKey($data['ai_agent_id'])
                ->where('is_active', true)
                ->firstOrFail();
        }

        $prospect->update(['ai_agent_id' => $data['ai_agent_id'] ?? null]);

        return response()->json([
            'success' => true,
            'ai_agent_id' => $prospect->ai_agent_id,
        ]);
    }
}
