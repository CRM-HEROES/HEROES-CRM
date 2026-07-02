<?php

namespace App\Filters;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;

class CommissionFilters extends Filters
{
    /**
     * Filter by order
     */
    protected function order(Builder $builder, $value)
    {
        $order = Order::find($value);

        $builder->where(function($query) use($order) {
            foreach ($order->actions as $action) {
                $query->orWhere(function($query) use($action) {
                    $query
                        ->where('action_id', $action->id)
                        ->where('user_id', $action->pivot->user_id);
                });
            }
        });
    }

    /**
     * Filter by actions
     */
    protected function actions(Builder $builder, $value)
    {
        $builder->whereIn('action_id', $value);
    }

    /**
     * Filter by products
     */
    protected function products(Builder $builder, $value)
    {
        $builder->whereIn('product_id', $value);
    }

    /**
     * Filter by users
     */
    protected function users(Builder $builder, $value)
    {
        $builder->whereIn('user_id', $value);
    }
}
