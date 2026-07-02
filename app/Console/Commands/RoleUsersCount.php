<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RoleUsersCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:role-users-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Role users count';

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
        $counts = DB::table('model_has_roles')
            ->select('role_id', DB::raw('count(*) as users_count'))
            ->groupBy('role_id')
            ->where('model_type', 'App\\Models\\User')
            ->get();

        $query = "UPDATE roles SET users_count = CASE ";
        $query .= $counts
            ->map(function($count) {
                return "WHEN id = {$count->role_id} THEN {$count->users_count} ";
            })
            ->join("");
        $query .= "ELSE 0 END ";
        $query .= "WHERE id IN (";
        $query .= $counts
            ->map(function($count) {
                return $count->role_id;
            })
            ->join(",");
        $query .= ")";

        DB::update($query);

        return self::SUCCESS;
    }

}
