<?php

namespace App\Http\Controllers\API\Project\Prospect\Order;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\Order;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Prospect $prospect, Order $order)
    {
        abort_unless($prospect->id == $order->prospect_id, 404);

        return $order
            ->products()
            ->with([
                'images' => function($query) {
                    $query->orderBy('order', 'asc');
                },
            ])
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Prospect $prospect, Order $order, Product $product)
    {
        abort_unless($prospect->id == $order->prospect_id, 404);
        abort_unless($project->id == $product->project_id, 404);

        // Check if all products in this order
        // have the same currency
        abort_unless($order->products->count() == 0 || $order->products->first()->pivot->currency == $product->currency, 404, trans("order.error.incompatible_currency"));
        

        if ($order->products()->find($product->id)) {
            $productInfos = array_merge($request->only(
                    'quantity', 
                    'currency', 
                    'including_tax', 
                    'price', 
                    'tax',
                    'meta'
                ),
                [
                    'updated_at' => \Carbon\Carbon::now()
                ]
            );
        } else {
            $productInfos = [
                // Use product infos as default values
                'quantity'      => $request->input('quantity', 1),
                'currency'      => $request->input('currency', $product->currency),
                'including_tax' => $request->input('including_tax', $product->including_tax),
                'price'         => $request->input('price', $product->price),
                'tax'           => $request->input('tax', $product->tax),
                'meta'          => $request->input('meta', null),
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ];
        }

        $order
            ->products()
            ->syncWithoutDetaching([$product->id => $productInfos]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Prospect $prospect, Order $order, Product $product)
    {
        abort_unless($prospect->id == $order->prospect_id, 404);
        abort_unless($project->id == $product->project_id, 404);

        $order->products()->detach($product->id);

        return ['message' => trans('common.success.detached_resource')];
    }
}
