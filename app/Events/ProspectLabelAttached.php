<?php

namespace App\Events;

use App\Models\Label;
use App\Models\Prospect;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProspectLabelAttached
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Prospect $prospect;
    public Label $label;

    /**
     * Create a new event instance.
     */
    public function __construct(Prospect $prospect, Label $label)
    {
        $this->prospect = $prospect;
        $this->label = $label;
    }
}
