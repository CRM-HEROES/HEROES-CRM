<?php

namespace App\Console\Commands\Campaign\Prospect;

use App\Console\Commands\Campaign\Action;
use App\Models\Calendar;
use App\Models\Event;
use App\Models\Prospect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Add event to prospect
 */
class ProspectEventAction extends Action
{
    /**
     * 
     */
    public function handle()
    {
        if (!$this->model instanceof Prospect) {
            return;
        }
        
        if (!$this->action->value) {
            return;
        }
        
        // Calendar

        if (!$this->action->value['calendar']) {
            return;
        }
        
        if (!($calendar = $this->getCalendar($this->action->value['calendar']))) {
            return;
        }


        // User
        
        if (!$this->action->value['user']) {
            return;
        }
        
        if (!($user = $this->getUser($this->action->value['user']))) {
            return;
        }


        // Date
        
        if (!$this->action->value['min_days']) {
            return;
        }
        
        if (!$this->action->value['max_days']) {
            return;
        }
        
        if (!$this->action->value['duration']) {
            return;
        }

        if (!($date = $this->getDate(
                $user, 
                \Carbon\Carbon::now()->addDay($this->action->value['min_days'])->setHour(8)->setMinute(0)->setSecond(0),
                \Carbon\Carbon::now()->addDay($this->action->value['max_days'])->setHour(20)->setMinute(0)->setSecond(0),
                $this->action->value['duration']
            )
        )) {
            return;
        }


        // Event
        
        $event = Event::create([
            'user_id' => $user,
            'calendar_id' => $calendar->id,
            'prospect_id' => $this->model->id,
            'name' => $this->model->full_name,
            'description' => "Automatic event by HeroesCRM",
            'location' => $this->model->address,
            'latitude' => $this->model->latitude,
            'longitude' => $this->model->longitude,
            'started_at' => $date,
            'ended_at' => $date->copy()->addMinutes($this->action->value['duration']),
        ]);
    }

    /**
     * 
     */
    protected function getCalendar($calendarId) {
        // Calednar
        return Calendar
            ::where('id', $calendarId)
            ->where('project_id', $this->model->project_id)
            ->first(['id']);
    }

    /**
     * 
     */
    protected function getUser($userId) {
        // Whoever created the prospect
        if ($userId == 'prospect-creator') {
            return $this->getUserCreator();
        // Whoever who is assigned to the prospect
        } else if ($userId == 'prospect-affected-user') {
            return $this->getUserAffected();
        // User having the given role
        } else if (Str::startsWith($userId, 'role.')) {
            return $this->getUserHavingRole(Str::replace('role.', '', $userId));
        // Random user
        } else if ($userId == 'random') {
            return $this->getUserRandom();
        }
        
        // User id
        return $this->getUserById($userId);
    }

    /**
     * Get the prospect creator
     */
    protected function getUserCreator() {
        return $this->model->creator_id;
    }

    /**
     * Get the prospect affected user
     */
    protected function getUserAffected() {
        // Get first user
        // affected to the prospect
        $user = $this->model->prospect
            ->users()
            ->first(['id']);

        if (!$user) {
            return null;
        }

        return $user->id;
    }

    /**
     * Get first user having role
     */
    protected function getUserHavingRole($roleId) {
        // Check if the given role
        // is associated to the prospect project
        $role = DB::table('roles')
            ->where('project_id', $this->model->project_id)
            ->where('id', $roleId)
            ->first(['id']);

        if (!$role) {
            return null;
        }
            
        // Get first user
        // having the role
        $users = DB::table('model_has_roles')
            ->where('role_id', $role->id)
            ->where('model_type', "App\\Models\\User")
            ->get(['model_id']);

        if (count($users) == 0) {
            return null;
        }
        
        return $users[rand(0, count($users) - 1)]->model_id;
    }

    /**
     * Get random user
     */
    protected function getUserRandom() {
        // Select all users
        // associated
        // to the prospect project
        $users = DB::table('user_project')
            ->where('project_id', $this->model->project_id)
            ->get(['user_id']);

        if (count($users) == 0) {
            return null;
        }
        
        return $users[rand(0, count($users) - 1)]->user_id;
    }

    /**
     * Get user by id
     */
    protected function getUserById($userId) {
        $user = DB::table('user_project')
            ->where('project_id', $this->model->project_id)
            ->where('user_id', $userId)
            ->first(['id']);

        if (!$user) {
            return null;
        }
        
        return $user->id;
    }

    /**
     * 
     */
    protected function getDate($userId, $minDate, $maxDate, $duration = 15) {
        $dates = $this->getAvailableDates($userId, $minDate, $maxDate, $duration);

        if (count($dates) == 0) {
            return null;
        }

        return $dates[rand(0, count($dates) - 1)];
    }
    
    /**
     * 
     */
    protected function getAvailableDates($userId, $minDate, $maxDate, $duration = 15) {
        $interval = $duration;
        $date = new \Carbon\Carbon($minDate);
        $lastDate = new \Carbon\Carbon($maxDate);
        $dates = [];

        $events = Event
            ::where('user_id', $userId)
            ->where('started_at', '<', $lastDate)
            ->where('ended_at', '>', $date)
            ->get();

        while ($date->lt($lastDate)) {
            /*$event = Event
                ::where('user_id', $userId)
                ->where(function($query) use($date, $duration) {
                    $query
                        ->where(function($query) use($date) {
                            $query
                                ->where('started_at', '<=', $date->format("Y-m-d H:i:s"))
                                ->where('ended_at', '>', $date->format("Y-m-d H:i:s"));
                        })
                        ->orWhere(function($query) use($date, $duration) {
                            $endDate = $date->copy()->addMinutes($duration)->format("Y-m-d H:i:s");
                            $query
                                ->where('started_at', '<=', $endDate)
                                ->where('ended_at', '>', $endDate);
                        });
                })
                ->first();*/

            $event = $events->filter(function($e) use($date, $duration) {
                $beginDate =  $date->format("Y-m-d H:i:s");
                $endDate = $date->copy()->addMinutes($duration)->format("Y-m-d H:i:s");

                return 
                    ($beginDate >= $e->started_at && $beginDate < $e->ended_at) ||
                    ($endDate > $e->started_at && $endDate <= $e->ended_at);
            });

            // There is an available time slot
            if (count($event) == 0) {
                $dates[] = $date->copy();
            }

            // Next time slot
            $date = $date->addMinutes($interval);

            // Avoid hour greater than 20
            if ($date->hour >= 20) {
                $date = $date->addDay()->setHour(8)->setMinute(0)->setSecond(0);
            }

            // Avoid Sunday
            if ($date->dayOfWeek == 0) {
                $date = $date->addDay();
            }

            // Avoid Saturday
            if ($date->dayOfWeek == 6) {
                $date = $date->addDays(2);
            }
        };

        return $dates;
    }
}