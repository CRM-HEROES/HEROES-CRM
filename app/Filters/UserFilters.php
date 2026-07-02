<?php

namespace App\Filters;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class UserFilters extends Filters
{
    
    /**
     * Filter by search query
     */
    public function query(Builder $builder, $value) {
        $values = explode(' ', preg_replace('/[\\s|\\+|-]+/', ' ', $value));

        return $builder->where(function($query) use($values) {
            // Searchable default fields
            $defaultFields = ['name', 'last_name', 'email'];

            foreach ($values as $value) {
                $value = strtolower(trim(str_replace("+", " ", $value)));

                $query->where(function($query) use($defaultFields, $value) {
                    foreach ($defaultFields as $field) {
                        $query->orWhere(DB::raw("LOWER(`$field`)"), 'LIKE', "%{$value}%");
                    }
                });
            }
        });
    }

    /**
     * Filter by id
     */
    public function ids(Builder $builder, $value) {
        $builder->whereIn('id', $value);
    }

    /**
     * Filter by projects
     */
    public function withProjects(Builder $builder, $value) {
        $builder->whereHas('projects', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by without projects
     */
    public function withoutProjects(Builder $builder, $value) {
        $builder->whereDoesntHave('projects', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by calendars
     */
    public function withCalendars(Builder $builder, $value) {
        $builder->whereHas('calendars', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by without calendars
     */
    public function withoutCalendars(Builder $builder, $value) {
        $builder->whereDoesntHave('calendars', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by categories
     */
    public function withCategories(Builder $builder, $value) {
        $builder->whereHas('categories', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by without categories
     */
    public function withoutCategories(Builder $builder, $value) {
        $builder->whereDoesntHave('categories', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by documents
     */
    public function withDocuments(Builder $builder, $value) {
        $builder->whereHas('documents', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by without documents
     */
    public function withoutDocuments(Builder $builder, $value) {
        $builder->whereDoesntHave('documents', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by folders
     */
    public function withFolders(Builder $builder, $value) {
        $builder->whereHas('folders', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by without folders
     */
    public function withoutFolders(Builder $builder, $value) {
        $builder->whereDoesntHave('folders', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by groups
     */
    public function withGroups(Builder $builder, $value) {
        $builder->whereHas('groups', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by without groups
     */
    public function withoutGroups(Builder $builder, $value) {
        $builder->whereDoesntHave('groups', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by menus
     */
    public function withMenus(Builder $builder, $value) {
        $builder->whereHas('menus', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by without menus
     */
    public function withoutMenus(Builder $builder, $value) {
        $builder->whereDoesntHave('menus', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by order actions
     */
    public function withOrderActions(Builder $builder, $value) {
        $builder->whereHas('orderActions', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by without order actions
     */
    public function withoutOrderActions(Builder $builder, $value) {
        $builder->whereDoesntHave('orderActions', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by order steps
     */
    public function withOrderSteps(Builder $builder, $value) {
        $builder->whereHas('orderSteps', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by without order steps
     */
    public function withoutOrderSteps(Builder $builder, $value) {
        $builder->whereDoesntHave('orderSteps', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by prospects
     */
    public function withProspects(Builder $builder, $value) {
        $builder->whereHas('prospects', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by without prospects
     */
    public function withoutProspects(Builder $builder, $value) {
        $builder->whereDoesntHave('prospects', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by roles
     */
    public function withRoles(Builder $builder, $value) {
        $builder->whereHas('roles', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by without roles
     */
    public function withoutRoles(Builder $builder, $value) {
        $builder->whereDoesntHave('roles', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by threads
     */
    public function withThreads(Builder $builder, $value) {
        $builder->whereHas('threads', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by without threads
     */
    public function withoutThreads(Builder $builder, $value) {
        $builder->whereDoesntHave('threads', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by users
     */
    public function withUsers(Builder $builder, $value) {
        $builder->whereHas('assignableUsers', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by without users
     */
    public function withoutUsers(Builder $builder, $value) {
        $builder->whereDoesntHave('assignableUsers', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by with vehicle
     */
    public function withVehicles(Builder $builder, $value) {
        $builder->whereHas('vehicles', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                // $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by without vehicle
     */
    public function withoutVehicles(Builder $builder, $value) {
        $builder->whereDoesntHave('vehicles', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                // $query->whereIn('id', $value);
            });
        });
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
    public function defaultField($builder, $slug, $value) {
        return $builder->where(DB::raw("LOWER(`$slug`)"), 'LIKE', "%{$value}%");
    }
}
