<?php

namespace App\Console\Commands;

use App\Jobs\VehiclePosition;
use App\Models\Vehicle;
use Illuminate\Console\Command;

class VehicleTracker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:vehicle-tracker {--ids=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Persists each user\'s daily tracks on the app';

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
        $vehicles = Vehicle::whereNotNull('user_id')->get();

        foreach ($vehicles as $vehicle) {
            if ($vehicle->tracker == 'optimum-automotive') {
                dispatch((new VehiclePosition($vehicle))->onQueue('google_map'));
            }
        }
    }

}
