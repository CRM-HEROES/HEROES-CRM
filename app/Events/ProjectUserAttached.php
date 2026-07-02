<?php

namespace App\Events;

use App\Models\Project;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectUserAttached
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Project $project;
    public User $user;

    /**
     * Create a new event instance.
     */
    public function __construct(Project $project, User $user)
    {
        $this->project = $project;
        $this->user = $user;
    }
}
