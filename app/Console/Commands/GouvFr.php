<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\GouvFr as ServicesGouvFr;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Console\Helper\ProgressBar;

class GouvFr extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:gouv-fr {--project=} {--departement=} {--region=} {--per_page=} {--from=1} {--to=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search entreprises from gouv.fr API';

    protected $mapping = [
        'first_name' => 'nom_complet',
        'street' => 'siege.adresse',
        'postal_code' => 'siege.code_postal',
        'city' => 'siege.libelle_commune',
        'latitude' => 'siege.latitude',
        'longitude' => 'siege.longitude',
        'meta->siren' => 'siren',
        'meta->siret_2' => 'siege.siret',
        'date_of_birth' => 'date_creation',
        'meta->nom_raison_sociale' => 'nom_raison_sociale',
        'meta->activite_principale' => 'siege.activite_principale',
        'meta->nombre_etablissements' => 'nombre_etablissements',
        'meta->nombre_etablissements_ouverts' => 'nombre_etablissements_ouverts',
        'meta->annee_categorie_entreprise' => 'annee_categorie_entreprise',
        'meta->annee_tranche_effectif_salarie' => 'siege.annee_tranche_effectif_salarie',
        'meta->tranche_effectif_salarie' => 'siege.tranche_effectif_salarie',
        'meta->date_debut_activite' => 'siege.date_debut_activite',
        'meta->categorie_entreprise' => 'categorie_entreprise',
        'meta->nature_juridique' => 'nature_juridique',
        'meta->dirigeant' => ['dirigeants.0.prenoms', 'dirigeants.0.nom'],
        'meta->est_association' => 'complements.est_association',
        'meta->est_bio' => 'complements.est_bio',
        'meta->est_entrepreneur_individuel' => 'complements.est_entrepreneur_individuel',
        'meta->est_entrepreneur_spectacle' => 'complements.est_entrepreneur_spectacle',
        'meta->est_ess' => 'complements.est_ess',
        'meta->est_finess' => 'complements.est_finess',
        'meta->est_organisme_formation' => 'complements.est_organisme_formation',
        'meta->est_qualiopi' => 'complements.est_qualiopi',
        'meta->est_rge' => 'complements.est_rge',
        'meta->est_service_public' => 'complements.est_service_public',
        'meta->est_siae' => 'complements.est_siae',
        'meta->est_societe_mission' => 'complements.est_societe_mission',
        'meta->est_uai' => 'complements.est_uai',
    ];

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
    public function handle(ServicesGouvFr $api)
    {
        $total = 0;
        $project = Project::where('slug', $this->option("project"))->first();

        if (!$project) {
            return;
        }

        $from = $this->option("from");
        $to = $this->option("to");

        if ($from > $to) {
            return;
        }

        $query = array_filter($this->options());
        unset($query['project']);
        unset($query['from']);
        unset($query['to']);

        $progressBar = $this->configureProgressBar($from, $to);
        $progressBar->start();

        $siren = $this->existingSiren($project);

        for ($page = $from; $page <= $to; ++$page) {
            $prospects = $this->searchCompanies($api, $project, $query, $page, $siren);
            $count = count($prospects);

            DB::table('prospects')->insert($prospects);

            $progressBar->setMessage("Page " . $page . ": " . $count . ' new entreprises.');
            $progressBar->advance();

            $total += $count;
        }

        $progressBar->setMessage('Finished: ' . $total . ' new entreprises.');
        $progressBar->finish();
    }

    protected function searchCompanies($api, $project, $query, $page, &$siren) {
        $query = array_merge($query, ['page' => $page]);

        $results = $api->searchCompanies($query);
        
        if (!$results || !isset($results['results'])) {
            return [];
        }

        $date = Carbon::now()->format('Y-m-d h:i:s');

        $prospects = [];
        foreach ($results['results'] as $entreprise) {
            if (in_array($entreprise['siren'], $siren)) {
                continue;
            } else {
                $siren[] = $entreprise['siren'];
            }
        
            $prospect = [
                'project_id' => $project->id,
                'created_at' => $date,
                'updated_at' => $date,
                'meta' => []
            ];

            // Mapping
            foreach ($this->mapping as $field => $key) {
                if (is_array($key)) {
                    $v = implode(' ', array_filter(array_map(function($k) use($entreprise) {
                        return data_get($entreprise, $k);
                    }, $key)));
                } else {
                    $v = data_get($entreprise, $key);
                }
                
                if (Str::startsWith($field, 'meta->')) {
                    $field = str_replace('meta->', '', $field);
                    $prospect['meta'][$field] = $v;
                } else {
                    $prospect[$field] = $v;
                }
            }

            $prospect['meta'] = json_encode($prospect['meta']);
            $prospect['valid_address'] = $prospect['latitude'] && $prospect['longitude'];
            $prospects[] = $prospect;
        }

        return $prospects;
    }
    
    /**
     * 
     */
    protected function configureProgressBar($from, $to)
    {
        ProgressBar::setFormatDefinition('custom', ' %current%/%max% [%bar%] %message%');

        $progressBar = $this->output->createProgressBar($to - $from + 1);
        $progressBar->setBarCharacter('|');
        $progressBar->setProgressCharacter('|');
        $progressBar->setEmptyBarCharacter('.');
        $progressBar->setFormat('custom');
        $progressBar->setMessage("Starting ...");

        return $progressBar;
    }

    /**
     * 
     */
    protected function existingSiren($project)
    {
        $result = [];

        $skip = 0;
        $take = 10000;

        do {
            $queryResult = $project->prospects()
                ->skip($skip)
                ->take($take)
                ->get(['meta->siren'])
                ->toArray();

            $siren = array_map(
                function($value) {
                    return $value["json_unquote(json_extract(`meta`, '$.\"siren\"'))"];
                }, 
                $queryResult
            );

            unset($queryResult);

            $result = array_merge($result, $siren);

            $count = count($siren);

            unset($siren);
            
            $skip += $take;
        } while ($count >= $take);

        return $result;
    }

}
