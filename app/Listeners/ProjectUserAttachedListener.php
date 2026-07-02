<?php

namespace App\Listeners;

use App\Events\ProjectUserAttached;
use App\Models\UserSetting;
use App\Utils\ProjectSetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProjectUserAttachedListener
{
    /**
     * Handle the event.
     */
    public function handle(ProjectUserAttached $event): void
    {
        $this->configureProspectsTableSetting($event->project, $event->user);
        $this->attachCalendars($event->project, $event->user);
    }

    /**
     * Configure default prospects table setting
     */
    protected function configureProspectsTableSetting($project, $user)
    {
        $key = 'prospects-table';
        $setting = ProjectSetting::get($project, $key, null);

        if (!$setting) {
            $setting = array_values($project->fields->filter(function($field) {
                return $field['for'] == 'prospect';
            })->map(function($field) {
                return [
                    'key' => ($field->meta ? 'meta->' : '') . $field->slug
                ];
            })->toArray());
        }

        UserSetting::updateOrCreate([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'key' => $key,
        ], [
            'value' => $setting,
            'creator_id' => null,
        ]);
    }

    /**
     * 
     */
    protected function createUserMenu($project, $user)
    {

    }

    /**
     * 
     */
    protected function attachCalendars($project, $user)
    {
        $user->calendars()->syncWithoutDetaching($project->calendars()->get(['id'])->pluck('id'));
    }
}
