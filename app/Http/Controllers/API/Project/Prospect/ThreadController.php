<?php

namespace App\Http\Controllers\API\Project\Prospect;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Prospect;
use Illuminate\Support\Facades\DB;

class ThreadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Prospect $prospect)
    {
        return DB::table('messages')
            ->join('threads', 'threads.id', '=', 'messages.thread_id')
            ->select('messages.thread_id', DB::raw('count(*) as total'))
            ->groupBy('messages.thread_id')
            ->where('messages.prospect_id', $prospect->id)
            ->where('threads.project_id', $project->id)
            ->get();
    }
}
