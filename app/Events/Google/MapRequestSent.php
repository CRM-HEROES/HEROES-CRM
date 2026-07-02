<?php
 
namespace App\Events\Google;
 
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
 
class MapRequestSent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
 
    /**
     * Create a new event instance.
     */
    public function __construct()
    {}
}