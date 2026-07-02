<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class DocumentScope implements Scope
{

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  IlluminateDatabaseEloquentBuilder  $builder
     * @param  IlluminateDatabaseEloquentModel  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
    	if (!auth()->user() || auth()->user()->is_super_admin || auth()->user()->is_project_admin || !getPermissionsTeamId()) {
            return;
    	}
    
        $builder->where(function($query) {
            $query
                ->whereHas('users', function($query) {
                    $query->where('id', auth()->id());
                })
                ->orWhereHas('roles', function($query) {
                    $query->whereIn('id', auth()->user()->roles->pluck('id'));
                });
        });
    }
}