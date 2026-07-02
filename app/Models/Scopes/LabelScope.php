<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

class LabelScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
    	if (!auth()->user() || auth()->user()->is_super_admin || auth()->user()->is_project_admin || !getPermissionsTeamId()) {
            return;
    	}

        $categories = DB::table('user_category')
            ->select('category_id')
            ->where('user_id', auth()->id())
            ->union(
                DB::table('role_category')
                ->select('category_id')
                ->join('model_has_roles', 'model_has_roles.role_id', '=', 'role_category.role_id')
                ->where('model_has_roles.model_id', auth()->id())
                ->where('model_has_roles.model_type', "App\Models\User")
            )
            ->get()
            ->map(function($category) {
                return $category->category_id;
            });

        $builder->where(function($query) use($categories) {
            $query
                ->whereHas('users', function($query) {
                    $query->where('id', auth()->id());
                })
                ->orWhereHas('roles', function($query) {
                    $query->whereIn('id', auth()->user()->roles->pluck('id'));
                })
                ->orWhereIn('category_id', $categories);
        });
    }
}
