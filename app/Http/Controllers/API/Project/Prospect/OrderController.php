<?php

namespace App\Http\Controllers\API\Project\Prospect;

use App\Filters\OrderRequestFilters;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Project;
use App\Models\Prospect;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Prospect $prospect, OrderRequestFilters $orderRequestFilters)
    {
        // abort_unless(auth()->user()->can('prospectOrderView', $project), 404);

        return $prospect
            ->orders()
            ->with([
                'products' => function($query) {},
                'products.images' => function($query) {
                    $query->orderBy('order', 'asc');
                }
            ])
            ->with('steps:id,name')
            ->with('actions:id,name')
            ->with('status:id,name')
            ->with('creator:id,name')
            ->filter($orderRequestFilters)
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project, Prospect $prospect)
    {
        // If order status is given
        // Make sure that the order status is associated to the current project
        abort_unless(!$request->has('status_id') || $project->orderStatuses()->find($request->input('status_id')), 404);
        abort_unless(auth()->user()->can('prospectOrderAdd', $project), 404);

        $order = $prospect
            ->orders()
            ->create(array_merge($request->only(
                'name',
                'description',
                'created_at',
                'status_id'
            ), [
                'creator_id' => auth()->id(),
            ]));

        $order->load(['actions' => function($query) {
            $query->select('id', 'name')->withPivot('user_id', 'paid');
        }]);
        
        return $order;
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Prospect $prospect, Order $order)
    {
        // Prospect associated to the order
        abort_unless($prospect->id == $order->prospect_id, 404);
        abort_unless(auth()->user()->can('prospectOrderView', $project), 404);

        $order->load([
            'products' => function($query) {},
            'products.images' => function($query) {
                $query->orderBy('order', 'asc');
            },
        ]);
        $order->load('steps:id,name');
        $order->load(['actions' => function($query) {
            $query->select('id', 'name')->withPivot('user_id', 'paid');
        }]);
        $order->load('invoices:id,document_id');
        $order->load('status:id,name');
        $order->load('creator:id,name');

        return $order;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Prospect $prospect, Order $order)
    {
        // Prospect associated to the order
        abort_unless($prospect->id == $order->prospect_id, 404);
        // If order status is given
        // Make sure that the order status is associated to the current project
        abort_unless(!$request->has('status_id') || $project->orderStatuses()->find($request->input('status_id')), 404);
        abort_unless(auth()->user()->can('prospectOrderUpdate', $project), 404);

        // Default values
        $defaultFieldValues = $request->only(
            'name',
            'description',
            'status_id'
        );
        
        // Meta values
        $metaFieldValues = collect($request->meta)->only(
            $project->fields()
                ->where('for', "order")
                ->where('meta', true)
                ->get()
                ->pluck('slug')
                ->toArray()
        )->toArray();

        // All values
        $values = array_merge(
            $defaultFieldValues, 
            [
                'meta' => array_merge(
                    $order->meta ?: [], 
                    $metaFieldValues
                )
            ]
                );

        $order->update($values);

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Prospect $prospect, Order $order)
    {
        // Prospect associated to the order
        abort_unless($prospect->id == $order->prospect_id, 404);
        abort_unless(auth()->user()->can('prospectOrderDelete', $project), 404);

        $order->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
