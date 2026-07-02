<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OrderNumber extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:order-number';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate orders number';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $orders = DB::table('orders')
            ->join('prospects', 'prospects.id', '=', 'orders.prospect_id')
            /*->where('number', 0)*/
            ->get(['orders.id', 'orders.created_at', 'prospects.project_id']);

        foreach ($orders as $order) {
            DB::table('orders')
                ->where('id', $order->id)
                // ->limit(1)
                ->update([
                    'number' => DB::table('orders')
                        ->join('prospects', 'prospects.id', '=', 'orders.prospect_id')
                        ->where('orders.created_at', '<', $order->created_at)
                        ->where('prospects.project_id', $order->project_id)
                        ->count() + 1
                ]);
        }
    }
}
