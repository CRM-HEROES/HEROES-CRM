<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LabelProspectsCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:label-prospects-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Label prospects count';

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

        $counts = DB::table('prospect_label')
            ->select('label_id', 'prospects.project_id', DB::raw('count(*) as prospects_count'))
            ->join('prospects', 'prospects.id', '=', 'prospect_label.prospect_id')
            ->groupBy('label_id', 'project_id')
            ->get();

        $query = "UPDATE labels SET prospects_count = CASE ";
        $query .= $counts
            ->map(function($count) {
                return "WHEN id = {$count->label_id} THEN {$count->prospects_count} ";
            })
            ->join("");
        $query .= "END ";
        $query .= "WHERE id IN (";
        $query .= $counts
            ->map(function($count) {
                return $count->label_id;
            })
            ->join(",");
        $query .= ")";

        DB::update($query);

        $stats = DB::table('stats')
            ->select('key', 'value', 'date')
            ->whereNull('user_id')
            ->where('key', 'LIKE', 'label.total.%')
            ->orderBy('date', 'desc')
            ->get();

        $stats = $stats->reduce(function($carry, $stat) {
            $labelId = str_replace('label.total.', '', $stat->key);

            if (!isset($carry[$labelId])) {
                $carry[$labelId] = $stat->value;
            }

            return $carry;
        }, []);

        $date = \Carbon\Carbon::now()->format("Y-m-d");
        $newStats = [];

        foreach ($counts as $count) {
            if (!isset($stats[$count->label_id]) || $stats[$count->label_id] != $count->prospects_count) {
                $newStats[] = [
                    'project_id' => $count->project_id,
                    'key' => 'label.total.' . $count->label_id,
                    'value' => $count->prospects_count,
                    'date' => $date,
                ];
            }
        }

        if (!empty($newStats)) {
            DB::table('stats')->insert($newStats);
        }

        return self::SUCCESS;
    }

}
