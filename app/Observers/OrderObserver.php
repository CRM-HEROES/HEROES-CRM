<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Prospect;
use Illuminate\Support\Facades\DB;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function creating(Order $order): void
    {
        $prospect = Prospect::withTrashed()->where('id', $order->prospect_id)->first();

        if (!$prospect) {
            return;
        }

        $order->number = DB::table('orders')
            ->join('prospects', 'prospects.id', '=', 'orders.prospect_id')
            ->where('prospects.project_id', $prospect->project_id)
            ->count() + 1;
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        $orderActions = $order
            ->prospect
            ->project
            ->orderActions()
            ->whereNotNull('default_role_id')
            ->with('defaultRole')
            ->get();

        foreach ($orderActions as $action) {
            $user = $action->defaultRole->users()->first();

            if (!$user) {
                continue;
            }

            $order
                ->actions()
                ->withPivot(['user_id', 'creator_id', 'created_at', 'updated_at'])
                ->syncWithoutDetaching([$action->id => [
                    'user_id'    => $user->id,
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ]]);
        }
    }
}
