<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderFilters extends Filters
{
    /**
     * Filter by id
     */
    public function ids(Builder $builder, $value) {
        $builder->whereIn('id', $value);
    }

    /**
     * Filter by created at
     */
    public function createdAt(Builder $builder, $value) {
        return $builder->whereBetween('created_at', [$value . ' 00:00:00', $value . ' 23:59:59']);
    }

    /**
     * Filter by created after
     */
    public function createdAfter(Builder $builder, $value) {
        return $builder->where('created_at', '>=', $value);
    }

    /**
     * Filter by created before
     */
    public function createdBefore(Builder $builder, $value) {
        return $builder->where('created_at', '<=', $value);
    }

    /**
     * Filter by created X days ago
     */
    public function createdDaysAgo(Builder $builder, $value) {
        return $builder->where('created_at', '>=', \Carbon\Carbon::now()->subDays($value)->format("Y-m-d h:i:s"));
    }

    /**
     * Filter by created before X days
     */
    public function createdBeforeDays(Builder $builder, $value) {
        return $builder->where('created_at', '<=', \Carbon\Carbon::now()->subDays($value)->format("Y-m-d h:i:s"));
    }

    /**
     * Filter by updated at
     */
    public function updatedAt(Builder $builder, $value) {
        return $builder->whereBetween('updated_at', [$value . ' 00:00:00', $value . ' 23:59:59']);
    }

    /**
     * Filter by updated after
     */
    public function updatedAfter(Builder $builder, $value) {
        return $builder->where('updated_at', '>=', $value);
    }

    /**
     * Filter by updated before
     */
    public function updatedBefore(Builder $builder, $value) {
        return $builder->where('updated_at', '<=', $value);
    }

    /**
     * Filter by updated X days ago
     */
    public function updatedDaysAgo(Builder $builder, $value) {
        return $builder->where('updated_at', '>=', \Carbon\Carbon::now()->subDays($value)->format("Y-m-d h:i:s"));
    }

    /**
     * Filter by updated before X days
     */
    public function updatedBeforeDays(Builder $builder, $value) {
        return $builder->where('updated_at', '<=', \Carbon\Carbon::now()->subDays($value)->format("Y-m-d h:i:s"));
    }

    /**
     * Filter by query
     */
    public function query(Builder $builder, $value)
    {
        $value = Str::lower($value);
        $builder->where(function ($query) use($value) {
            foreach (['name', 'description'] as $field) {
                return $query->where(DB::raw("LOWER(`$field`)"), 'LIKE', "%{$value}%");
            }
        });
    }

    /**
     * Filter by creator
     */
    public function withCreators(Builder $builder, $value) {
        return $builder->whereHas('creators', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $userFilters = new UserFilters();

                foreach ($value as $k => $v) {
                    $userFilters->{$k}($query, $v);
                }
            });
        });
    }

    /**
     * Filter by without creator
     */
    public function withoutCreators(Builder $builder, $value) {
        return $builder->whereDoesntHave('creators', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $userFilters = new UserFilters();

                foreach ($value as $k => $v) {
                    $userFilters->{$k}($query, $v);
                }
            });
        });
    }

    /**
     * Filter by prospect
     */
    public function withProspects(Builder $builder, $value) {
        return $builder->whereHas('prospects', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $prospectFilters = new ProspectFilters();

                foreach ($value as $k => $v) {
                    $prospectFilters->{$k}($query, $v);
                }
            });
        });
    }

    /**
     * Filter by without prospects
     */
    public function withoutProspects(Builder $builder, $value) {
        return $builder->whereDoesntHave('prospects', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $prospectFilters = new ProspectFilters();

                foreach ($value as $k => $v) {
                    $prospectFilters->{$k}($query, $v);
                }
            });
        });
    }

    /**
     * Search by product
     */
    public function withProducts($builder, $value) {
        return $builder->whereHas('products', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->where('id', $value);
            });
        });
    }

    /**
     * Search by product
     */
    public function withoutProducts($builder, $value) {
        return $builder->whereDoesntHave('products', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->where('id', $value);
            });
        });
    }

    /**
     * Search by status
     */
    public function withStatuses($builder, $value) {
        return $builder
            ->when(!empty($value), function($query) use($value) {
                $query->whereIn('status_id', $value);
            })
            ->when(count($value) == 0, function($query) {
                $query->whereNotNull('status_id');
            });
    }

    /**
     * Search by status
     */
    public function withoutStatuses($builder, $value) {
        return $builder
            ->when(!empty($value), function($query) use($value) {
                $query->whereNotIn('status_id', $value);
            })
            ->when(count($value) == 0, function($query) {
                $query->whereNull('status_id');
            });
    }

    /**
     * Search by step
     */
    public function withSteps($builder, $value) {
        return $builder->whereHas('steps', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->where('id', $value);
            });
        });
    }

    /**
     * Search by without step
     */
    public function withoutSteps($builder, $value) {
        return $builder->whereDoesntHave('steps', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->where('id', $value);
            });
        });
    }

    /**
     * Search by action
     */
    public function withActions($builder, $value) {
        return $builder->whereHas('actions', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->where('id', $value);
            });
        });
    }
    
    /**
     * Search by action
     */
    public function withoutActions($builder, $value) {
        return $builder->whereDoesntHave('actions', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->where('id', $value);
            });
        });
    }
    
    /**
     * Search by document
     */
    public function withDocuments($builder, $value) {
        return $builder->whereHas('invoices', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->where('document_id', $value);
            });
        });
    }
    
    /**
     * Search by document
     */
    public function withoutDocuments($builder, $value) {
        return $builder->whereDoesntHave('invoices', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->where('document_id', $value);
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
    protected function defaultField($builder, $slug, $value)
    {
        $value = Str::lower($value);
        return $builder->where(DB::raw("LOWER(`$slug`)"), 'LIKE', "%{$value}%");
    }
}
