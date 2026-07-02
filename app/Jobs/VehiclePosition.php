<?php

namespace App\Jobs;

use App\Models\Vehicle;
use App\Services\Google\Map;
use App\Services\OptimumAutomotive;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class VehiclePosition implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Vehicle $vehicle;

    /**
     * Create a new job instance.
     */
    public function __construct(Vehicle $vehicle)
    {
        $this->vehicle = $vehicle;
    }

    /**
     * Execute the job.
     */
    public function handle(OptimumAutomotive $optimumAutomotive): void
    {
        $position = $this->position($optimumAutomotive);

        if (!$position) {
            return;
        }

        $this->vehicle->update(array_merge($position, [
            'position_at' => \Carbon\Carbon::now()
        ]));

        $position = DB::table('vehicle_positions')
            ->orderBy('created_at', 'desc')
            ->first(['latitude', 'longitude']);

        if (
            !$position || 
            $position->latitude != $this->vehicle->latitude || 
            $position->longitude != $this->vehicle->longitude || 
            $position->user_id != $this->vehicle->user_id
        ) {
            DB::table('vehicle_positions')
                ->insert([
                    'vehicle_id' => $this->vehicle->id,
                    'user_id' => $this->vehicle->user_id,
                    'latitude' => $this->vehicle->latitude,
                    'longitude' => $this->vehicle->longitude,
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ]);
        } else if ($position) {
            DB::table('vehicle_positions')
                ->where('vehicled_id', $this->vehicle->id)
                ->where('user_id', $this->vehicle->user_id)
                ->where('created_at', $position->created_at)
                ->update([
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ]);
        }
    }
    
    /**
     * Get vehicle position
     *
     * @param  $address
     * @return void
     */
    protected function position(OptimumAutomotive $optimumAutomotive)
    {
        switch ($this->vehicle->tracker) {
            case 'optimum-automotive':
                return $this->optimumAutomotive($optimumAutomotive);
            default:
                return null;
        }
    }

    /**
     * Get vehicle position
     * usign Optimum automotive
     *
     * @param  $address
     * @return void
     */
    protected function optimumAutomotive(OptimumAutomotive $optimumAutomotive)
    {
        if (!$this->vehicle->data || !isset($this->vehicle->data['idVH'])) {
            return null;
        }

        $result = $optimumAutomotive->GetPosition([
            'idtracker' => $this->vehicle->data['idVH'],
        ]);

        if (!isset($result->GetPositionResult)) {
            return null;
        }

        $position = $result->GetPositionResult;

        return [
            "latitude" => $position->latitude,
            "longitude" => $position->longitude,
        ];
    }

}
