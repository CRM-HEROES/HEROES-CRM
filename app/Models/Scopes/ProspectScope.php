<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ProspectScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
    	if (
            !auth()->user() || 
            auth()->user()->is_super_admin || 
            auth()->user()->is_project_admin || 
            !getPermissionsTeamId() ||
            (request()->project && auth()->user()->can('prospectView', request()->project))
        ) {
            return;
    	}

        // Select only prospects
        $builder->where(function($query) {
            $query
                ->where('creator_id', auth()->id())
                // associated to the current user
                ->orWhereHas('users', function($query) {
                    $query->where('id', auth()->id());
                })
                // or associated to any current user group
                ->orWhereHas('groups', function($query) {
                    $query->whereIn('id', auth()->user()->groups->pluck('id')->toArray());
                });
        });
    }
}
