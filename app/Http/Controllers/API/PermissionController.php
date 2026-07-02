<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        abort_unless(auth()->user(), 401);

        return auth()->user()->getAllPermissions()->pluck('name');
    }
}
