<?php

namespace App\Console\Commands\Campaign\Order;

use App\Console\Commands\Campaign\Action;
use App\Models\Order;

/**
 * Delete an order
 * 
 * Data format: none
 */
class OrderDeleteAction extends Action
{
    /**
     * 
     */
    public function handle()
    {
        if (!$this->model instanceof Order) {
            return;
        }
        
        $this->model->delete();
    }
}