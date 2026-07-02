<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\User;
use App\Services\Google\Map;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class UserGetEventsDailyDirection implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $date;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, $date)
    {
        $this->user = $user;
        $this->date = $date;
    }

    /**
     * Execute the job.
     */
    public function handle(Map $map): void
    {
        $events = $this->getUserEvents();

        if (count($events) == 0) {
            return;
        }

        $this->setSearching(true);

        // Single event searching
        if (count($events) == 1) {
            $this->singleEventPosition($map, $events[0]);
        } else {
            $this->multipleEventsDirection($map, $events);
        }
        
        $this->setSearching(false);
    }

    /**
     * Get user events between $begin and $end.
     *
     * @return void
     */
    protected function getUserEvents()
    {
        return DB::table('events')
            ->select('events.id', 'location')
            ->join('calendars', 'events.calendar_id', '=', 'calendars.id')
            ->where('user_id', $this->user->id)
            // ->where('calendars.project_id', $this->project->id)
            ->where('calendars.type', "physical")
            ->where('events.location', '!=', "")
            ->where('events.started_at', '>=', $this->date . ' 00:00:00')
            ->where('events.ended_at', '<=', $this->date . ' 23:59:59')
            ->whereNull('events.deleted_at')
            ->orderBy('events.started_at')
            ->limit(24)
            ->get()
            ->toArray();
    }

    /**
     * Searching
     *
     * @return void
     */
    protected function setSearching($searching)
    {
        DB::table('user_events_daily_directions')->updateOrInsert([
            'user_id'      => $this->user->id,
            // 'project_id'   => $this->project->id,
            'direction_at' => $this->date,
        ], [
            'searching' => $searching,
        ]);
    }

    /**
     * Get a single event position
     *
     * @return void
     */
    protected function singleEventPosition(Map $map, $event)
    {
        try {
            $search = $map->find($event->location);

            DB::table('user_events_daily_directions')->updateOrInsert([
                'user_id'      => $this->user->id,
                // 'project_id'   => $this->project->id,
                'direction_at' => $this->date,
            ], [
                'direction' => json_encode($search),
                'events' => json_encode([$event->id]),
            ]);
        } catch (\Exception $e) {
            // \ProjectLog::warning($this->project, $e->getMessage());
        }
    }

    /**
     * Get multiple events direction
     *
     * @return void
     */
    protected function multipleEventsDirectionParam($events)
    {
        return [
            array_shift($events)->location,
            array_pop($events)->location,
            array_map(function($event) {
                return $event->location;
            }, $events),
        ];
    }

    /**
     * Get multiple events direction
     *
     * @return void
     */
    protected function multipleEventsDirection(Map $map, $events)
    {
        list($origin, $destination, $waypoints) = $this->multipleEventsDirectionParam($events);

        try {
            $search = $map->direction($origin, $destination, $waypoints);
            
            DB::table('user_events_daily_directions')->updateOrInsert([
                'user_id'      => $this->user->id,
                // 'project_id'   => $this->project->id,
                'direction_at' => $this->date,
            ], [
                'direction' => json_encode($search),
                'events' => json_encode(array_map(function($event) {
                    return $event->id;
                }, $events)),
            ]);
        } catch (\Exception $e) {
            // \ProjectLog::warning($this->project, $e->getMessage());
        }
    }
}
