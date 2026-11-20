<?php

namespace App\Console\Commands;

use App\Workflows\Mailing\PlanGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MailingGenerateWorkflows extends Command
{
    protected $signature = 'mailing:generate-workflows {project : id of the project} {path : folder of the kit (catalogue.json)} {--dry-run : check only, nothing is written}';

    protected $description = 'Draw the emails of a mailing plan as automations (drafts) of a project: its sequences, its emails around an appointment, its newsletter and its emails sent on demand';

    public function handle(PlanGenerator $generator): int
    {
        $project = DB::table('projects')->where('id', $this->argument('project'))->first(['id', 'name']);

        if (!$project) {
            $this->error('Projet introuvable.');

            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');
        $this->info(($dry ? '[simulation] ' : '') . "Projet #{$project->id} {$project->name}");

        $report = $generator->generate($project->id, rtrim($this->argument('path'), '/\\'), $dry);

        $this->line('Étiquettes créées : ' . count($report['labels']['created']));

        $byStatus = [];
        $withProblems = 0;

        foreach ($report['workflows'] as $name => $workflow) {
            $byStatus[$workflow['status']] = ($byStatus[$workflow['status']] ?? 0) + 1;

            // The whole list with -v, otherwise only what needs a look
            if ($this->getOutput()->isVerbose() || $workflow['problems']) {
                $this->line("  {$name} : {$workflow['status']}, {$workflow['nodes']} étapes");
            }

            foreach (array_slice($workflow['problems'], 0, 5) as $problem) {
                $this->warn("      - $problem");
            }

            $withProblems += $workflow['problems'] ? 1 : 0;
        }

        $this->line('Automatisations : ' . count($report['workflows']));

        foreach ($byStatus as $status => $count) {
            $this->line("  $count $status");
        }

        $withProblems
            ? $this->warn("$withProblems automatisation(s) à corriger avant de pouvoir être activées (voir ci-dessus).")
            : $this->info('Aucune erreur : tout peut être vérifié puis activé.');

        return self::SUCCESS;
    }
}
