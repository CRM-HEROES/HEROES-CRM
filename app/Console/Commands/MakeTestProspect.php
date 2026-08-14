<?php

namespace App\Console\Commands;

use App\Models\Interaction;
use App\Models\Project;
use App\Models\Prospect;
use Illuminate\Console\Command;

/**
 * Generates a fully-populated, clearly-flagged test prospect, so testing
 * a new feature (AI quote, AI phone agent, ...) doesn't require manually
 * retyping fake data in tinker every time. All prospects created this
 * way are tagged in meta (is_test_prospect) so they can be found and
 * wiped in bulk with --clean.
 */
class MakeTestProspect extends Command
{
    protected $signature = 'app:make-test-prospect {project?} {--clean : Delete every prospect previously created by this command instead of creating a new one}';
    protected $description = 'Create (or clean up) a fully-populated test prospect for manually testing new features.';

    public function handle()
    {
        if ($this->option('clean')) {
            return $this->clean();
        }

        $projectArg = $this->argument('project');
        $project = $projectArg
            ? (Project::find($projectArg) ?: Project::where('slug', $projectArg)->first())
            : Project::first();

        if (!$project) {
            $this->error($projectArg ? "Projet introuvable : {$projectArg}" : "Aucun projet en base.");
            return self::FAILURE;
        }

        $faker = \Faker\Factory::create('fr_FR');
        $firstName = $faker->firstName();
        $lastName = $faker->lastName();

        $prospect = Prospect::withoutGlobalScopes()->create([
            'project_id' => $project->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => \Illuminate\Support\Str::slug($firstName . '.' . $lastName, '.') . '@test.heroes-crm.local',
            'phone_number' => $faker->phoneNumber(),
            'mobile_phone_number' => $faker->phoneNumber(),
            'company_name' => $faker->company(),
            'job_title' => $faker->jobTitle(),
            'website_url' => 'https://' . $faker->domainName(),
            'street' => $faker->streetAddress(),
            'postal_code' => $faker->postcode(),
            'city' => $faker->city(),
            'country' => 'France',
            'meta' => [
                'is_test_prospect' => true,
                'ai_phone_agent_last_analysis' => [
                    'summary' => 'Prospect intéressé, budget confirmé, prêt à recevoir une proposition.',
                    'qualification' => 'hot',
                    'needs' => ['gestion des prospects', 'automatisation des relances'],
                    'objections' => [],
                    'next_steps' => ['envoyer un devis', 'planifier une démo'],
                    'budget' => $faker->numberBetween(1500, 8000) . ' euros',
                    'project' => 'Mise en place d\'un CRM pour ' . $faker->randomElement(['la vente en ligne', 'la gestion commerciale', 'le suivi client']),
                ],
            ],
        ]);

        $this->info("Prospect de test créé : #{$prospect->id} ({$firstName} {$lastName}, projet {$project->name}).");
        $this->line("Pour le supprimer avec les autres prospects de test : php artisan app:make-test-prospect --clean");

        return self::SUCCESS;
    }

    private function clean()
    {
        $ids = Prospect::withoutGlobalScopes()
            ->get(['id', 'meta'])
            ->filter(fn (Prospect $p) => data_get($p->meta, 'is_test_prospect') === true)
            ->pluck('id');

        if ($ids->isEmpty()) {
            $this->info('Aucun prospect de test à nettoyer.');
            return self::SUCCESS;
        }

        Interaction::whereIn('prospect_id', $ids)->delete();
        Prospect::withoutGlobalScopes()->whereIn('id', $ids)->forceDelete();

        $this->info(count($ids) . ' prospect(s) de test supprimé(s) : ' . $ids->implode(', '));

        return self::SUCCESS;
    }
}
