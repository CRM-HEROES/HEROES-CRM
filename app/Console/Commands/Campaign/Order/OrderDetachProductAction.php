<?php

namespace App\Console\Commands\Campaign\Order;

use App\Console\Commands\Campaign\Action;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Detach product from an order
 * 
 * Data format:
 * {
 *    product: {product ID}
 * }
 */
class OrderDetachProductAction extends Action
{
    /**
     * 
     */
    public function handle()
    {
        if (
            !$this->model instanceof Order ||
            !$this->action->value ||
            !isset($this->action->value['product']) || !$this->action->value['product']
        ) {
            return null;
        }

        // Order step
        $product = DB::table('products')
            ->where('id', $this->action->value['product'])
            ->where('project_id', $this->model->prospect->project_id)
            ->first(['id']);
    
        if (!$product) {
            return null;
        }

        $this->model
            ->products()
            ->detach($product->id);
    }
}