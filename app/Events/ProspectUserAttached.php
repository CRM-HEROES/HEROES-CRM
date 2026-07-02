<?php

namespace App\Events;

use App\Models\User;
use App\Models\Prospect;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProspectUserAttached
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Prospect $prospect;
    public User $user;

    /**
     * Create a new event instance.
     */
    public function __construct(Prospect $prospect, User $user)
    {
        $this->prospect = $prospect;
        $this->user = $user;
    }
}
