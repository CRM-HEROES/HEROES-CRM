<?php

namespace App\Http\Controllers\API\Project\Prospect;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Project;
use App\Models\Prospect;
use Illuminate\Support\Facades\DB;

class FolderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Prospect $prospect)
    {
        return DB::table('files')
            ->join('folders', 'folders.id', '=', 'files.folder_id')
            ->select('files.folder_id', DB::raw('count(*) as total'))
            ->groupBy('files.folder_id')
            ->where('files.prospect_id', $prospect->id)
            ->whereNull('files.deleted_at')
            ->where('folders.project_id', $project->id)
            ->get();
    }
}
