<?php

namespace App\Observers;

use App\Models\Calendar;
use Illuminate\Support\Facades\DB;

class CalendarObserver
{
    /**
     * Handle the Calendar "creating" event.
     */
    public function creating(Calendar $calendar): void
    {
        $calendar->order = $this->getOrder($calendar);
    }

    /**
     * Handle the Calendar "updating" event.
     */
    public function updating(Calendar $calendar): void
    {
        $this->updateOrder($calendar);
    }

    /**
     * Handle the Calendar "deleted" event.
     */
    public function deleted(Calendar $calendar): void
    {
        $this->deleteOrder($calendar);
    }

    /**
     * Get order
     *
     * @param  \App\Models\Calendar  $calendar
     * @return void
     */
    protected function getOrder(Calendar $calendar)
    {
        return Calendar
            ::where('project_id', $calendar->project_id)
            ->count() + 1;
    }

    /**
     * Update order
     *
     * @param  \App\Models\Calendar  $calendar
     * @return void
     */
    protected function updateOrder(Calendar $calendar)
    {
        if (!$calendar->isDirty('order')) {
            return;
        }

        $count = DB::table('calendars')
            ->where('project_id', $calendar->project_id)
            ->count();

        $calendar->order = max(1, min($calendar->order, $count));

        $oldOrder = $calendar->getOriginal('order');
        $newOrder = $calendar->order;

        if ($oldOrder < $newOrder) {
            DB::table('calendars')
                ->where('project_id', $calendar->project_id)
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $newOrder)
                ->update([
                    'order' => DB::raw('`order` - 1')
                ]);
        } else if ($oldOrder > $newOrder) {
            DB::table('calendars')
                ->where('project_id', $calendar->project_id)
                ->where('order', '>=', $newOrder)
                ->where('order', '<', $oldOrder)
                ->update([
                    'order' => DB::raw('`order` + 1')
                ]);
        }
    }

    /**
     * Delete order
     *
     * @param  \App\Models\Calendar  $calendar
     * @return void
     */
    protected function deleteOrder(Calendar $calendar)
    {
        DB::table('calendars')
            ->where('project_id', $calendar->project_id)
            ->where('order', '>=', $calendar->order)
            ->update([
                'order' => DB::raw('`order` - 1')
            ]);
    }
}
