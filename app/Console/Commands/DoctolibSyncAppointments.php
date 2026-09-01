<?php

namespace App\Console\Commands;

use App\Jobs\Doctolib\AppointmentsGet;
use App\Models\DoctolibAccount;
use Illuminate\Console\Command;

/**
 * Dispatches App\Jobs\Doctolib\AppointmentsGet for every active Doctolib
 * account (or a specific one/project via the options). Not scheduled yet
 * in App\Console\Kernel — see the commented-out entry there — since the
 * job itself has no real data source configured until Doctolib API/ICS
 * access is available.
 */
class DoctolibSyncAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:doctolib-sync-appointments {--project=} {--account=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch appointments from Doctolib and import them as events (stub, see App\Jobs\Doctolib\AppointmentsGet)';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach ($this->getActiveAccounts() as $account) {
            AppointmentsGet::dispatch($account, $account->project)->onQueue('imports');
        }
    }

    /**
     * Get all Doctolib active accounts
     */
    protected function getActiveAccounts()
    {
        return DoctolibAccount
            // If project command parameter
            // is given
            ::when($this->option('project'), function ($query) {
                $query->where('project_id', $this->option('project'));
            })
            // If account command parameter
            // is given
            ->when($this->option('account'), function ($query) {
                $query->where('id', $this->option('account'));
            })
            ->with('project:id,name,slug')
            ->get();
    }
}
