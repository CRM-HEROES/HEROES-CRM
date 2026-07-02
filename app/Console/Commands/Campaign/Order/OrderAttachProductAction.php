<?php

namespace App\Console\Commands\Campaign\Order;

use App\Console\Commands\Campaign\Action;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Attach product to an order
 * 
 * Data format:
 * {
 *    product: {
 *       id: {product ID},
 *       quantity: {quantity, default 1},
 *       currency: {currency, default product currency},
 *       including_tax: {including tax, default product price including tax},
 *       price: {price, default product price},
 *       tax: {tax, default product tax},
 *    }
 * }
 */
class OrderAttachProductAction extends Action
{
    /**
     * 
     */
    public function handle()
    {
        if (
            !$this->model instanceof Order ||
            !$this->action->value ||
            !isset($this->action->value['product']) || !$this->action->value['product'] || !is_array($this->action->value['product']) ||
            !isset($this->action->value['product']['id']) || !$this->action->value['product']['id']
        ) {
            throw new \Exception("Unkown product");
        }

        // Order product
        $product = DB::table('products')
            ->where('id', $this->action->value['product']['id'])
            ->where('project_id', $this->model->prospect->project_id)
            ->first(['id', 'currency', 'including_tax', 'price', 'tax']);
    
        if (!$product) {
            throw new \Exception("Unkown product");
        }

        $this->model
            ->products()
            ->syncWithoutDetaching([$product->id => [
                'quantity'      => data_get($this->action->value['product'], 'quantity', 1),
                'currency'      => data_get($this->action->value['product'], 'currency', $product->currency),
                'including_tax' => data_get($this->action->value['product'], 'including_tax', $product->including_tax),
                'price'         => data_get($this->action->value['product'], 'price', $product->price),
                'tax'           => data_get($this->action->value['product'], 'tax', $product->tax),
                'created_at'    => \Carbon\Carbon::now(),
                'updated_at'    => \Carbon\Carbon::now(),
            ]]);
    }
}