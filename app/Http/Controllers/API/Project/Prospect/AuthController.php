<?php

namespace App\Http\Controllers\API\Project\Prospect;

use App\Http\Controllers\Controller;
use App\Jobs\ProspectAuth;
use App\Models\Project;
use App\Models\Prospect;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Prospect $prospect)
    {
        ProspectAuth::dispatch($prospect)->onConnection('sync');
    }
}
