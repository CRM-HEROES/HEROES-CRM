<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\CloudTalkCallAttribution;
use Illuminate\Console\Command;

class AttributeCloudTalkCalls extends Command
{
    protected $signature = 'cloudtalk:attribute-calls {project : Slug du projet} {--dry-run : Afficher les correspondances sans modifier les appels}';

    protected $description = 'Rattacher les appels CloudTalk sans créateur aux utilisateurs configurés dans les lignes du projet';

    public function handle(): int
    {
        $project = Project::where('slug', $this->argument('project'))->first();
        if (!$project) {
            $this->error('Projet introuvable.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $result = (new CloudTalkCallAttribution($project))->backfill($dryRun);
        $this->info($dryRun
            ? "Simulation : {$result['matched']} appel(s) peuvent être rattachés."
            : "{$result['updated']} appel(s) rattachés.");

        if ($result['unmatched']) {
            $this->warn('Appels restants : association absente ou ambiguë, ou agent absent des données.');
            $rows = [];
            foreach ($result['unmatched'] as $agentId => $count) {
                $rows[] = [(string) $agentId, $count];
            }
            $this->table(['Agent CloudTalk', 'Appels sans utilisateur'], $rows);
        }

        return self::SUCCESS;
    }
}
