<?php

namespace App\Filters;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProjectFilters extends Filters
{
    /**
     * Filter by id
     */
    protected function ids(Builder $builder, $value)
    {
        $value = explode(',', $value);

        $builder->whereIn('id', $value);
    }

    /**
     * Filter by search query
     */
    public function query(Builder $builder, $value) {
        return $builder->where(DB::raw("LOWER(`name`)"), 'LIKE', "%{$value}%");
    }

    /**
     * Filter by user
     */
    protected function users(Builder $builder, $value)
    {
        $value = explode(',', $value);

        $builder->whereHas('users', function($query) use($value) {
            $query->whereIn('id', $value);
        });
    }

    /**
     * Filter by is admin
     */
    protected function admin(Builder $builder, $value)
    {
        $userProjectsQuery = DB::table('projects')
            ->join('model_has_permissions', 'projects.id', '=', 'model_has_permissions.project_id')
            ->join('permissions', 'permissions.id', '=', 'model_has_permissions.permission_id')
            ->where('model_has_permissions.model_id', auth()->id())
            ->where('model_has_permissions.model_type', 'App\Models\User')
            ->where('permissions.name', 'all');
            
        $roleProjectsQuery = DB::table('projects')
            ->join('roles', 'projects.id', '=', 'roles.project_id')
            ->join('role_has_permissions', 'roles.id', '=', 'role_has_permissions.role_id')
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->join('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', auth()->id())
            ->where('model_has_roles.model_type', 'App\Models\User')
            ->where('permissions.name', 'all');

        if (!auth()->user()->is_super_admin) {
            if ($value) {
                $builder->where(function($query) use($userProjectsQuery, $roleProjectsQuery) {
                    $query
                        ->where('projects.creator_id', auth()->id())
                        ->orWhereIn('projects.id', 
                            $userProjectsQuery->get(['projects.id'])->pluck('id')->merge(
                                $roleProjectsQuery->get(['projects.id'])->pluck('id')
                            )
                        );
                });
            } else {
                $builder->where(function($query) use($userProjectsQuery, $roleProjectsQuery) {
                    $query
                        ->where('projects.creator_id', '!=', auth()->id())
                        ->whereNotIn('projects.id', 
                            $userProjectsQuery->get(['projects.id'])->pluck('id')->merge(
                                $roleProjectsQuery->get(['projects.id'])->pluck('id')
                            )
                        );
                });
            }
        }
    }

    /**
     * Filter by user
     */
    protected function type(Builder $builder, $value)
    {
        $builder->whereIn('type', $value);
    }

    /**
     * Search by position
     */
    protected function position($builder, $value)
    {
        list($lat, $lng, $km) = explode(',', $value);

        if (empty($lat) || empty($lng) || empty($km)) {
            return $builder;
        }
    
        $raw = "(111.111 *" .
            " DEGREES(ACOS(LEAST(1.0, COS(RADIANS(?))" .
            " * COS(RADIANS(projects.latitude))" .
            " * COS(RADIANS(? - projects.longitude))" .
            " + SIN(RADIANS(?))" .
            " * SIN(RADIANS(projects.latitude)))))) <= ?";

        return $builder->whereRaw($raw, [$lat, $lng, $lat, $km]);
    }
    
    /**
     * Dynamic function call
     */
    public function __call($method, $args)
    {
        // Default field search
        if (Str::startsWith($method, 'field_')) {
            return $this->defaultField(
                $args[0],
                Str::replace('field_', '', $method),
                $args[1]
            );
        }
        
        throw new \BadMethodCallException("Method $method does not exist");;
    }

    /**
     * Search by default field
     */
    protected function defaultField($builder, $slug, $value)
    {
        return $builder->where(DB::raw("LOWER(`$slug`)"), 'LIKE', "%{$value}%");
    }
}
