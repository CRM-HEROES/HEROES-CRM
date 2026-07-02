<?php

namespace App\Console\Commands\Campaign\Prospect;

use App\Console\Commands\Campaign\Action;
use App\Models\Prospect;
use Illuminate\Support\Facades\DB;

/**
 * Add order to the prospect
 * 
 * Data format:
 * {
 *    status: {status of the new order},
 *    products: [
 *       // list of products
 *      {
 *         id: {product ID},
 *         quantity: {product quantity},
 *      },
 *      ...
 *    ],
 * }
 */
class ProspectOrderAction extends Action
{
    /**
     * 
     */
    public function handle()
    {
        if (
            !$this->model instanceof Prospect ||
            !$this->action->value ||
            // !isset($this->action->value['status']) || !$this->action->value['status'] ||
            !isset($this->action->value['products']) || !$this->action->value['products']
        ) {
            return null;
        }

        // Order Status
        /*$status = DB::table('order_status')
            ->where('id', $this->action->value['status'])
            ->where('project_id', $this->model->project_id)
            ->first(['id']);
    
        if (!$status) {
            return null;
        }*/

        // Products
        $products = DB::table('products')
            ->whereIn('id', array_keys($this->action->value['products']))
            ->where('project_id', $this->model->project_id)
            ->get(['id', 'name', 'currency', 'including_tax', 'price', 'tax']);

        $order = $this->model
            ->orders()
            ->create([
                'name' => $products->pluck('name')->join(", "),
                // 'status_id' => $status->id,
            ]);

        $now = \Carbon\Carbon::now();
        $orderProducts = [];
        foreach ($products as $product) { 
            $p = $this->action->value['products'][$product->id];

            $orderProducts[$product->id] = [
                'quantity' => data_get($p, 'quantity', 1),
                'currency' => data_get($p, 'currency', $product->currency),
                'including_tax' => data_get($p, 'including_tax', $product->including_tax),
                'price' => data_get($p, 'price', $product->price),
                'tax' => data_get($p, 'tax', $product->tax),
                'created_at' => $now,
                'updated_at' => $now
            ];
        }
        
        $order->products()->syncWithoutDetaching($orderProducts);
    }
}