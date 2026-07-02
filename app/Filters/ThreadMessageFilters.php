<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ThreadMessageFilters extends Filters
{
    public function withUsers(Builder $builder, $value)
    {
        $builder->whereHas('users', function($query) use($value) {
            $query->whereNull('user_message.archived_at');
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value['ids']);
            });
        });
    }
}
