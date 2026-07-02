<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return [
            'users' => $this->usersCount(),
            'prospects' => $this->prospectsCount(),
            'projects' => $this->projectsCount(),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function projects()
    {
        return auth()->user()->projects()->select('id', 'name', 'slug')->orderBy('user_project.order')->get();
    }

    /**
     * Count all users
     */
    protected function usersCount() {
        $cacheKey = 'user.count';

        return cache()->remember(
            $cacheKey, 
            3600, 
            function() {
                return User::count();
            });
    }

    /**
     * Count all projects
     */
    protected function projectsCount() {
        $cacheKey = 'project.count';

        return cache()->remember(
            $cacheKey, 
            3600, 
            function() {
                return Project::count();
            });
    }

    /**
     * Count all projects
     */
    protected function prospectsCount() {
        $cacheKey = 'prospect.count';

        return cache()->remember(
            $cacheKey, 
            3600, 
            function() {
                return Prospect::count();
            });
    }
}
