<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class UserScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
    	if (!auth()->user() || auth()->user()->is_super_admin || !getPermissionsTeamId()) {
            return;
    	}
    
    	if (auth()->user()->is_project_admin) {
            $builder->where(function($query) {
                $query
                    ->where('role', '!=', 'super_admin')
                    ->orWhereHas('assigningUsers', function($query) {
                        $query->where('id', auth()->id());
                    });
            });
    	}
    
        $builder->where(function($query) {
            $query
                ->whereHas('assigningUsers', function($query) {
                    $query->where('id', auth()->id());
                })
                /*->orWhereHas('assigningRoles', function($query) {
                    $query->whereIn('id', auth()->user()->roles->pluck('id'));
                })*/;
        });
    }
}
