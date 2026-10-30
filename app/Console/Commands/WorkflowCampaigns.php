<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Workflows\Legacy\LegacyCampaignConverter;
use Illuminate\Console\Command;

class WorkflowCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workflows:campaigns {--project= : Only the campaigns of this project (id)} {--notes : Show what changes for the campaigns that can be converted}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Report which legacy campaigns can be converted to workflows, and why not for the others';

    /**
     * Read only: nothing is converted, nothing is changed.
     */
    public function handle(LegacyCampaignConverter $converter)
    {
        $campaigns = Campaign::query()
            ->when($this->option('project'), fn ($query, $project) => $query->where('project_id', $project))
            ->orderBy('project_id')
            ->orderBy('id')
            ->get();

        if ($campaigns->isEmpty()) {
            $this->info('No campaign.');
            return self::SUCCESS;
        }

        $labels = [
            LegacyCampaignConverter::CONVERTED => 'déjà convertie',
            LegacyCampaignConverter::CONVERTIBLE => 'convertible',
            LegacyCampaignConverter::MANUAL => 'reste en campagne',
            LegacyCampaignConverter::FINISHED => 'terminée',
        ];

        $rows = [];
        $details = [];

        foreach ($campaigns as $campaign) {
            $analysis = $converter->analyze($campaign);

            $rows[] = [
                $campaign->id,
                $campaign->project_id,
                mb_substr($campaign->name, 0, 40),
                $campaign->for,
                $campaign->frequency,
                $campaign->active ? 'oui' : 'non',
                $labels[$analysis['status']],
            ];

            foreach ($analysis['reasons'] as $reason) {
                $details[] = "#{$campaign->id}: $reason";
            }

            if ($this->option('notes')) {
                foreach ($analysis['notes'] as $note) {
                    $details[] = "#{$campaign->id}: $note";
                }
            }
        }

        $this->table(['ID', 'Projet', 'Nom', 'Pour', 'Fréquence', 'Active', 'État'], $rows);

        foreach ($details as $line) {
            $this->line($line);
        }

        return self::SUCCESS;
    }
}
