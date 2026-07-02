<?php

namespace App\Console\Commands;

use App\Jobs\Pipedrive\PersonsAdd;
use App\Models\PipedriveAccount;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PipedriveAddPersons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:pipedrive-add-persons {--project=} {--account=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bulk add persons into pipedrive account';

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
        foreach ($this->getActiveAccounts() as $account) {
            $skip = 0;
            $take = 500;

            do {
                $prospects = DB::table('prospects')

                    // Prospects that not
                    // added to the pipedrive account "$account"
                    ->leftJoin('pipedrive_person', function($join) use($account) {
                        $join->on('prospects.id', '=', 'pipedrive_person.prospect_id');
                        $join->on('pipedrive_person.pipedrive_account_id', '=', DB::raw($account->id));
                    })
                    ->whereNull('pipedrive_person.person_id')

                    // Associated to account project
                    ->where('project_id', $account->project_id)

                    // Pagination
                    ->skip($skip)
                    ->take($take)

                    // Get
                    ->get(['id'])
                    ->pluck('id')
                    ->toArray();
                
                PersonsAdd::dispatch($account, $prospects)->onQueue("imports");
            } while (count($prospects) >= $take);
        }
    }

    /**
     * Get all Pipedrive active accounts
     */
    protected function getActiveAccounts() {
        return PipedriveAccount
            // If project command parameter
            // is given
            ::when($this->option('project'), function($query) {
                $query->where('project_id', $this->option('project'));
            })
            // If account command parameter
            // is given
            ->when($this->option('account'), function($query) {
                $query->where('id', $this->option('account'));
            })
            ->with('project:id,name,slug')
            ->get();
    }
}
