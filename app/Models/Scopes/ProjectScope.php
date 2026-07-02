<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ProjectScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
    	if (!auth()->user() || auth()->user()->is_super_admin/* || auth()->user()->is_project_admin*/) {
            return;
    	}
    
        $builder->where(function($query) {
            $query
                ->whereHas('users', function($query) {
                    $query->where('id', auth()->id());
                });
        });
    }
}
