<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\Pappers as PappersAPI;
use App\Services\PappersImmo as PappersImmoAPI;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Console\Helper\ProgressBar;

class Pappers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:pappers {--project=} {--bases=} {--champs_supplementaires=} {--parcelle_cadastrale=} {--code_postal=} {--code_commune=} {--region=} {--departement=} {--latitude=} {--longitude=} {--distance=} {--type_syndic_copropriete=} {--siret_syndic_professionnel_copropriete=} {--nom_copropriete=} {--nombre_lots_copropriete_min=} {--nombre_lots_copropriete_max=} {--siren_proprietaire=} {--tranche_effectif_proprietaire_min=} {--tranche_effectif_proprietaire_max=} {--categorie_juridique_proprietaire=} {--code_naf_proprietaire=} {--denomination_proprietaire=} {--nombre_proprietaires_min=} {--nombre_proprietaires_max=} {--siren_occupant=} {--tranche_effectif_occupant_min=} {--tranche_effectif_occupant_max=} {--categorie_juridique_occupant=} {--code_naf_occupant=} {--denomination_occupant=} {--nature_mutation_vente=} {--type_local_vente=} {--date_mutation_vente_min=} {--date_mutation_vente_max=} {--prix_vente_min=} {--prix_vente_max=} {--surface_bati_vente_min=} {--surface_bati_vente_max=} {--surface_terrain_vente_min=} {--surface_terrain_vente_max=} {--nombre_pieces_vente_min=} {--nombre_pieces_vente_max=} {--annee_construction_batiment_min=} {--annee_construction_batiment_max=} {--nombre_logements_batiment_min=} {--nombre_logements_batiment_max=} {--surface_batiment_min=} {--surface_batiment_max=} {--nature_batiment=} {--usage_batiment=} {--classe_bilan_dpe=} {--type_installation_chauffage_dpe=} {--type_energie_chauffage_dpe=} {--prix_fonds_de_commerce_min=} {--prix_fonds_de_commerce_max=} {--date_fonds_de_commerce_min=} {--date_fonds_de_commerce_max=} {--code_naf_fonds_de_commerce=} {--statut_permis=} {--date_autorisation_permis_min=} {--date_autorisation_permis_max=} {--nombre_permis_min=} {--nombre_permis_max=} {--page=} {--par_page=}';

    /**
     * Current date
     */
    protected $date;

    /**
     * Prospect mapping from Pappers Immo API result
     */
    protected $immoMapping = [
        'street' => 'adresse',
        'postal_code' => 'code_commune',
        'city' => 'commune',

        'first_name' => 'proprietaires.0.personnes_physiques.0.nom_patronymique',
        'last_name' => 'proprietaires.0.personnes_physiques.0.prenoms',
        
        'meta->nom_dirigeant' => 'proprietaires.0.personnes_physiques.0.nom_patronymique',
        'meta->prenom_dirigeant' => 'proprietaires.0.personnes_physiques.0.prenoms',
        'meta->natonalite_dirigeant' => 'proprietaires.0.personnes_physiques.0.nationalite',
        'meta->date_de_naissance_dirigeant' => 'proprietaires.0.personnes_physiques.0.date_de_naissance',
        
        'company_name' => 'proprietaires.0.nom_entreprise',
        'meta->siren' => 'proprietaires.0.siren',
        'meta->tranche_effectifs' => 'proprietaires.0.tranche_effectifs',
        'meta->annee_effectifs' => 'proprietaires.0.annee_effectifs',
        'meta->activite_principale' => 'proprietaires.0.activite_principale',
        'meta->date_de_creation' => 'proprietaires.0.date_creation',
        'meta->categorie_juridique' => 'proprietaires.0.categorie_juridique',

        'meta->numero_parcelle' => 'numero',
        'meta->section' => 'section',
        'meta->prefixe' => 'prefixe',
        'meta->numero_plan' => 'numero_plan',
        'meta->contenance' => 'contenance',
        
        'meta->top_left_lat' => 'top_left.lat',
        'meta->top_left_lon' => 'top_left.lon',
        'meta->bottom_right_lat' => 'bottom_right.lat',
        'meta->bottom_right_lon' => 'bottom_right.lon',
        'meta->geometrie' => 'geometrie',
    ];

    /**
     * Prospect mapping from Pappers API result
     */
    protected $companyMapping = [
        'mobile_phone_number' => 'telephone',
        'email' => 'email',
        'latitude' => 'siege.latitude',
        'longitude' => 'siege.longitude',

        'meta->nic' => 'siege.nic',
        'meta->code_naf' => 'siege.code_naf',
        'meta->libelle_code_naf' => 'siege.libelle_code_naf',
        'meta->effectif_min' => 'siege.effectif_min',
        'meta->effectif_max' => 'siege.effectif_max',
        'meta->domaine_activite' => 'domaine_activite',
        'meta->objet_social' => 'objet_social',
        'meta->forme_juridique' => 'forme_juridique',
        'meta->forme_exercice' => 'forme_exercice',
        'meta->capital' => 'capital',
        'meta->devise_capital' => 'devise_capital',
        'meta->duree_personne_morale' => 'duree_personne_morale',
        'meta->derniere_mise_a_jour_sirene' => 'derniere_mise_a_jour_sirene',
        'meta->derniere_mise_a_jour_rcs' => 'derniere_mise_a_jour_rcs',
        'meta->derniere_mise_a_jour_rne' => 'derniere_mise_a_jour_rne',
        'meta->dernier_traitement' => 'dernier_traitement',
        'meta->date_debut_activite' => 'date_debut_activite',
        'meta->date_debut_premiere_activite' => 'date_debut_premiere_activite',
        'meta->statut_rcs' => 'statut_rcs',
        'meta->greffe' => 'greffe',
        'meta->code_greffe' => 'code_greffe',
        'meta->numero_rcs' => 'numero_rcs',
        'meta->date_immatriculation_rcs' => 'date_immatriculation_rcs',
        'meta->date_premiere_immatriculation_rcs' => 'date_premiere_immatriculation_rcs',
        'meta->date_radiation_rcs' => 'date_radiation_rcs',
        'meta->statut_rne' => 'statut_rne',
        'meta->date_immatriculation_rne' => 'date_immatriculation_rne',
        'meta->date_radiation_rne' => 'date_radiation_rne',
        'meta->numero_tva_intracommunautaire' => 'numero_tva_intracommunautaire',
        'meta->categorie_entreprise' => 'categorie_entreprise',
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get commpanies from pappers';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->date = Carbon::now()->format('Y-m-d h:i:s');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(PappersAPI $pappersApi, PappersImmoAPI $pappersImmoApi)
    {
        // Project
        $project = Project::where('slug', $this->option("project"))->first();
        if (!$project) {
            $this->error('Project not found.');
            return;
        }

        // Query filters
        $query = array_filter($this->options());
        unset($query['project']);

        // Sirens
        $siren = $this->existingSiren($project);

        // List of companies
        $prospects = $this->searchCompanies($pappersApi, $pappersImmoApi, $project, $query, $siren);

        // Insert prospects into the project database
        DB::table('prospects')->insert($prospects);
      
    }

    /**
     * 
     */
    protected function searchCompanies($pappersApi, $pappersImmoApi, $project, $query, &$siren) {

        // New company
        $newProspect = [
            'project_id' => $project->id,
            'created_at' => $this->date,
            'updated_at' => $this->date,
            'street' => null,
            'postal_code' => null,
            'city' => null,
            'first_name' => null,
            'last_name' => null,
            'company_name' => null,
            'mobile_phone_number' => null,
            'email' => null,
            'latitude' => null,
            'longitude' => null,
            'valid_address' => null,
            'meta' => [],
        ];
/*
        $disk = Storage::disk('documents');
        $contents = $disk->get('pappers-immo.json');
        $results = json_decode($contents, true);
*/

        // Pappers Immo API call

        $results = $pappersImmoApi->getPlots($query);
        if (!$results || !isset($results['resultats'])) {
            $this->error('No Immo results.');
            $this->error(json_encode($results));
            return [];
        }
  
        // Loop through results
        $prospects = [];
        foreach ($results['resultats'] as $parcelle) {
            $this->info('Parcelle: ' . $parcelle['numero']);

            if (!isset($parcelle['proprietaires']) || count($parcelle['proprietaires']) == 0) {
                $this->warn('No parcelle proprietaires.');
                continue;
            }
            
            $owner = $parcelle['proprietaires'][0];
            
            // Check if SIREN already in DB
            if (in_array($owner['siren'], $siren)) {
                $this->warn('SIREN already in DB: ' . $owner['siren']);
                continue;
            }
                
            try {
                // $entreprise = json_decode($disk->get('pappers-company.json'), true);
                $entreprise = $pappersApi->showCompany($owner['siren'], ['champs_supplementaires' => "telephone,email"]);
            } catch (\Exception $e) {
                $this->error('Pappers API call error.');
                continue;
            }

            if (!$entreprise) {
                $this->error('Pappers API call error null.');
                continue;
            }

            // New company
            $prospect = $newProspect;

            // Get fields values from $parcelle
            $prospect = $this->mapProspect($prospect, $parcelle, $this->immoMapping);
            $prospect = $this->mapProspect($prospect, $entreprise, $this->companyMapping);

            $prospect['valid_address'] = $prospect['latitude'] && $prospect['longitude'];
            $prospect['meta'] = json_encode($prospect['meta']);

            $prospects[] = $prospect;
            $siren[] = $owner['siren'];
        }

        return $prospects;
    }
    
    /**
     * Prograss Bar
     */
    protected function mapProspect(&$prospect, &$entreprise, &$mapping)
    {
        // Mapping fields
        foreach ($mapping as $field => $key) {
            // Get field value from $entreprise
            $v = data_get($entreprise, $key, null);

            if (!$v) {
                continue;
            }
            
            // Map to meta field
            if (Str::startsWith($field, 'meta->')) {
                $field = str_replace('meta->', '', $field);
                $prospect['meta'][$field] = $v;
            // Map to default field
            } else {
                $prospect[$field] = $v;
            }
        }

        return $prospect;
    }
    
    /**
     * Prograss Bar
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
                ->whereNotNull('meta->siren')
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
