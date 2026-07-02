<?php

namespace App\Http\Controllers\API\Project\API;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class PappersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Request $request)
    {
        $filters = [
            "bases",
            "parcelle_cadastrale",
            "code_postal",
            "code_commune",
            "region",
            "departement",
            "latitude",
            "longitude",
            "distance",

            "type_syndic_copropriete",
            "siret_syndic_professionnel_copropriete",
            "nom_copropriete",
            "nombre_lots_copropriete_min",
            "nombre_lots_copropriete_max",

            "siren_proprietaire",
            "tranche_effectif_proprietaire_min",
            "tranche_effectif_proprietaire_max",
            "categorie_juridique_proprietaire",
            "code_naf_proprietaire",
            "denomination_proprietaire",
            "nombre_proprietaires_min",
            "nombre_proprietaires_max",

            "siren_occupant",
            "tranche_effectif_occupant_min",
            "tranche_effectif_occupant_max",
            "categorie_juridique_occupant",
            "code_naf_occupant",
            "denomination_occupant",

            "nature_mutation_vente",
            "type_local_vente",
            "date_mutation_vente_min",
            "date_mutation_vente_max",
            "prix_vente_min",
            "prix_vente_max",
            "surface_bati_vente_min",
            "surface_bati_vente_max",
            "surface_terrain_vente_min",
            "surface_terrain_vente_max",
            "nombre_pieces_vente_min",
            "nombre_pieces_vente_max",

            "annee_construction_batiment_min",
            "annee_construction_batiment_max",
            "nombre_logements_batiment_min",
            "nombre_logements_batiment_max",
            "surface_batiment_min",
            "surface_batiment_max",
            "nature_batiment",
            "usage_batiment",

            "classe_bilan_dpe",
            "type_installation_chauffage_dpe",
            "type_energie_chauffage_dpe",

            "prix_fonds_de_commerce_min",
            "prix_fonds_de_commerce_max",
            "date_fonds_de_commerce_min",
            "date_fonds_de_commerce_max",
            "code_naf_fonds_de_commerce",

            "statut_permis",
            "date_autorisation_permis_min",
            "date_autorisation_permis_max",
            "nombre_permis_min",
            "nombre_permis_max",

            "page",
            "par_page"
        ];

        $this->validate($request, [
            'page' => 'required|integer',
            'par_page' => 'required|integer|max:100',
        ]);

        $filterValues = array_merge($request->only($filters), [
            'project' => $project->slug,
            'bases' => "proprietaires",
            'champs_supplementaires' => "proprietaires.personnes_physiques,bounding_box",
        ]);

        $parameters = [];
        foreach ($filterValues as $key => $value) {
            $parameters["--{$key}"] = $value;
        }

        // return $parameters;

        Artisan::call('app:pappers', $parameters);
    }
}
