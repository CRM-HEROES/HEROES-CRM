<?php

namespace App\Observers;

use App\Models\UserSetting;

class UserSettingObserver
{
    /**
     * Handle the Calendar "creating" event.
     */
    public function created(UserSetting $userSetting): void
    {
        $this->attachCategories($userSetting);
        $this->attachThreads($userSetting);
    }

    /**
     * Handle the Calendar "updating" event.
     */
    public function updated(UserSetting $userSetting): void
    {
        $this->attachCategories($userSetting);
        $this->attachThreads($userSetting);
    }

    /**
     * 
     */
    protected function attachCategories(UserSetting $userSetting) {
        if (
            $userSetting->key != 'prospects-table' || 
            !$userSetting->project || 
            (
                auth()->user() && 
                !auth()->user()->can('', $userSetting->project)
            )
        ) {
            return;
        }
    
        $categoryIds = array_map(function($value) {
            return str_replace('category->', '', $value['key']);
        }, array_filter($userSetting->value, function($value) {
            return (!isset($value['hidden']) || !$value['hidden']) && substr($value['key'], 0, 10) == 'category->';
        }));

        $categoryIds = $userSetting
            ->project
            ->categories()
            ->whereIn('id', $categoryIds)
            ->get(['id'])
            ->pluck('id');

        $userSetting->user->categories()->syncWithoutDetaching($categoryIds);
    }

    /**
     * 
     */
    protected function attachThreads(UserSetting $userSetting) {
        if (
            $userSetting->key != 'prospects-table' || 
            !$userSetting->project || 
            (
                auth()->user() && 
                !auth()->user()->can('', $userSetting->project)
            )
        ) {
            return;
        }
    
        $threadIds = array_map(function($value) {
            return str_replace('thread->', '', $value['key']);
        }, array_filter($userSetting->value, function($value) {
            return (!isset($value['hidden']) || !$value['hidden']) && substr($value['key'], 0, 8) == 'thread->';
        }));

        $threadIds = $userSetting
            ->project
            ->threads()
            ->whereIn('id', $threadIds)
            ->get(['id'])
            ->pluck('id');

        $userSetting->user->threads()->syncWithoutDetaching($threadIds);
    }
}
