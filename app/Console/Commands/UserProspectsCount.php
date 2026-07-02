<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UserProspectsCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:user-prospects-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'User prospects count';

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
        $counts = DB::table('prospect_user')
            ->join('prospects', 'prospects.id', '=', 'prospect_user.prospect_id')
            ->select('prospect_user.user_id', 'prospects.project_id', DB::raw('count(*) as prospects_count'))
            ->groupBy('prospect_user.user_id', 'prospects.project_id')
            ->get();

        $query = "UPDATE user_project SET prospects_count = CASE ";
        $query .= $counts
            ->map(function($count) {
                return "WHEN user_id = {$count->user_id} AND project_id = {$count->project_id} THEN {$count->prospects_count} ";
            })
            ->join("");
        $query .= "ELSE 0 END ";
        $query .= "WHERE user_id IN (";
        $query .= $counts
            ->map(function($count) {
                return $count->user_id;
            })
            ->join(",");
        $query .= ")";
        $query .= " AND project_id IN (";
        $query .= $counts
            ->map(function($count) {
                return $count->project_id;
            })
            ->join(",");
        $query .= ")";

        DB::update($query);

        return self::SUCCESS;
    }

}
