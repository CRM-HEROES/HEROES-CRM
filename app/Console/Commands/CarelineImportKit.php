<?php

namespace App\Console\Commands;

use App\Workflows\Mailing\KitImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CarelineImportKit extends Command
{
    protected $signature = 'mailing:import-kit {project : id of the project} {path : folder of the kit (catalogue.json, templates/, config.json)} {--dry-run : check only, nothing is written} {--allow-placeholder : accept a config.json that is not complete (for a test)}';

    protected $description = 'Put a mailing plan made for Brevo into a project: its fields, its email templates and the sending rules';

    public function handle(KitImporter $importer): int
    {
        $project = DB::table('projects')->where('id', $this->argument('project'))->first(['id', 'name']);

        if (!$project) {
            $this->error('Projet introuvable.');

            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');
        $this->info(($dry ? '[simulation] ' : '') . "Projet #{$project->id} {$project->name}");

        $report = $importer->import($project->id, rtrim($this->argument('path'), '/\\'), [
            'dry_run' => $dry,
            'allow_placeholder' => (bool) $this->option('allow-placeholder'),
        ]);

        $this->line('Champs créés : ' . (count($report['fields']) ?: 'aucun'));
        $this->line("Modèles : {$report['templates']['created']} créés, {$report['templates']['updated']} mis à jour");
        $this->line('Règles d\'envoi : ' . ($report['settings'] ? 'ajoutées' : 'déjà présentes'));

        foreach ($report['skipped'] as $code => $why) {
            $this->line("  ignoré $code : $why");
        }

        foreach ($report['problems'] as $code => $problems) {
            foreach ((array) $problems as $problem) {
                $this->warn("  " . (is_string($code) ? "$code : " : '') . $problem);
            }
        }

        return $report['problems'] ? self::FAILURE : self::SUCCESS;
    }
}
