<?php

namespace App\Jobs\Import\ProspectItemsHandler;

use App\Jobs\Import\ProspectItemsHandlerInterface;
use App\Models\Import;
use Illuminate\Support\Facades\DB;

class OrdersHandler implements ProspectItemsHandlerInterface
{
    /**
     * 
     */
    public function handle(Import $import, &$prospectsIds, &$items, $date)
    {
        // check that $prospectsIds and $items arrays
        // have the same size
        if (count($prospectsIds) != count($items)) {
            throw new \Exception('Prospects ids count and prospects orders count don\'t match!');
        }

        $data = [];
        $prospectsIdsWithOrders = [];

        foreach ($items as $index => $orders) {
            $prospectId = $prospectsIds[$index];

            // $orders: orders to associate to $prospectId
            foreach ($orders as $order) {
                $data[] = [
                    'prospect_id' => $prospectId,
                    'name'  => mb_substr($order['name'], 0, 50),
                    'created_at'  => $order['date'],
                ];

                $prospectsIdsWithOrders[] = $prospectId;
            }
        }

        DB::table('orders')->insert($data);
        
        // Get orders ids
        $ordersIds = DB::table('orders')
            ->select('id')
            ->whereIn('prospect_id', array_unique($prospectsIdsWithOrders))
            ->orderBy('id')
            ->get(['id'])
            ->toArray();
            
        // Retrieve only ids
        $ordersIds = array_map(function($data) {
            return $data->id;
        }, $ordersIds);

        $products = [];
        $steps = [];
        $actions = [];
        $i = 0;
        foreach ($items as $orders) {

            // $orders: orders to associate to $prospectId
            foreach ($orders as $order) {
                $orderId = $ordersIds[$i];

                foreach ($order['products'] as $product) {
                    $products[] = [
                        'order_id' => $orderId,
                        'product_id'  => $product['id'],
                        'price'  => $product['price'],
                        'tax'  => $product['tax'],
                        'including_tax'  => $product['including_tax'],
                        'currency'  => $product['currency'],
                        'quantity'  => $product['quantity'],
                    ];
                }

                foreach ($order['steps'] as $step) {
                    $steps[] = [
                        'order_id' => $orderId,
                        'step_id'  => $step
                    ];
                }

                foreach ($order['actions'] as $action) {
                    $actions[] = [
                        'order_id' => $orderId,
                        'action_id'  => $action['action'],
                        'user_id'  => $action['user'],
                    ];
                }

                ++$i;
            }
        }
        
        DB::table('order_product')->insert($products);
        DB::table('order_step')->insert($steps);
        DB::table('order_action')->insert($actions);
    }
}