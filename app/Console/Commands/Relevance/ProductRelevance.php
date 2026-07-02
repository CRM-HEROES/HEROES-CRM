<?php

namespace App\Console\Commands\Relevance;

use App\Console\Commands\Relevance\Relevance;
use Illuminate\Support\Facades\DB;

class ProductRelevance extends Relevance
{
    /**
     *
     */
    public function getData($from)
    {
        return DB::table('order_product')
            ->select(DB::raw('product_id as item_id'), DB::raw('count(*) as relevance'))
            ->groupBy('product_id')
            ->where('created_at', '>=', $from)
            ->get();
    }

    /**
     *
     */
    public function getTable()
    {
        return "products";
    }

    /**
     *
     */
    public function getName()
    {
        return "Product relevance";
    }

}
