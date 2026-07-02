<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SmsFilters extends Filters
{
    /**
     * Filter by creator
     */
    public function withCreators(Builder $builder, $value) {
        return $builder->whereHas('creator', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $userFilters = new UserFilters();

                foreach ($value as $k => $v) {
                    $userFilters->{$k}($query, $v);
                }
            });
        });
    }

    /**
     * Filter by having error
     */
    public function error(Builder $builder, $value) {
        return $builder->{$value ? 'whereNotNull' : 'whereNull'}('error');
    }

    /**
     * Filter by having source
     */
    public function withSources(Builder $builder, $value) {
        return $builder->whereIn('source', $value);
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
     * Filter by sent at
     */
    public function sent(Builder $builder, $value) {
        return $builder->{$value ? 'whereNotNull' : 'whereNull'}('sent_at');
    }

    /**
     * Filter by sent at
     */
    public function sentAt(Builder $builder, $value) {
        return $builder->whereBetween('sent_at', [$value . ' 00:00:00', $value . ' 23:59:59']);
    }

    /**
     * Filter by sent after
     */
    public function sentAfter(Builder $builder, $value) {
        return $builder->where('sent_at', '>=', $value);
    }

    /**
     * Filter by sent before
     */
    public function sentBefore(Builder $builder, $value) {
        return $builder->where('sent_at', '<=', $value);
    }

    /**
     * Filter by query
     */
    public function query(Builder $builder, $value)
    {
        $builder->where(DB::raw("LOWER(`message`)"), 'LIKE', "%{$value}%");
    }
}
