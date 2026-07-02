<?php

namespace App\Http\Controllers\API\Project\Prospect\Order;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class CommissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Order $order)
    {
        abort_unless($project->id == $order->project_id, 404);

        // commission =
        // order_product.quantity * commissions.value * (commissions.type == 'fix' ? 1 : (products.price * (commissions.including_tax ? 1 : products.tax)))
        return DB::table('order_action')
            ->select(
                'order_product.quantity',
                'order_product.price',
                'order_product.tax',
                'order_product.including_tax',
                'commissions.value',
                'commissions.type',
                'commissions.including_tax'
            )
            ->join('order_product', 'order_action.order_id', '=', 'order_product.order_id')
            ->join('products', 'order_product.product_id', '=', 'products.id')
            ->join('commissions', function($join) {
                $join
                    ->on('products.id', '=', 'commissions.product_id')
                    ->where('order_action.action_id', '=', 'commissions.action_id')
                    ->where('order_action.user_id', '=', 'commissions.user_id');
            })
            ->where('order_action.order_id', $order->id)
            ->get();
    }
}
