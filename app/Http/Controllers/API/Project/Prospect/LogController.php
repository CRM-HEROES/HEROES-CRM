<?php

namespace App\Http\Controllers\API\Project\Prospect;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Prospect;
use App\Utils\Tracker\Log;
use Illuminate\Support\Str;

class LogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Prospect $prospect)
    {
        $logs = Log
            ::with([
                'session' => function ($query) {
                    $query->select('id', 'user_id');
                },
                'session.user' => function ($query) {
                    $query->select('id', 'name');
                },
                'routePath' => function ($query) {
                    $query->select('id', 'path');
                }
            ])
            ->whereHas('session', function ($query) {
                $query->whereNotNull('user_id');
            })
            ->whereHas('routePath', function ($query) use ($prospect) {
                $query
                    ->whereHas('route', function ($query) {
                        $query->whereIn('name', ['api.project.prospect.show']);
                    })
                    ->where('parameters->prospect', $prospect->id);
            })
            ->latest()
            ->paginate(25);

        return $logs;
    }

}
