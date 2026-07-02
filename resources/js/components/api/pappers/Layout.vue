<template>
    <div class="hc-flex-column" style="height: 100%">
        <item-list class="hc-flex-1" padding="5px">
            <tree-layout
                v-for="group in filters"
                :key="group.name"
                class="hc-pappers-filters-group"
                :tabulation="5"
                :open="group.open"
            >
                <template #header>
                    <item>
                        <icon class="fa fa-filter" color="#333333" />
                        <div
                            class="hc-item-main-content"
                            v-text="group.name"
                        ></div>
                        <icon class="fa fa-caret-down" />
                    </item>
                </template>
                <template #body>
                    <item-list class="hc-flex-1" padding="5px">
                        <h-field
                            v-for="filter in group.filters"
                            :key="filter.key"
                            :label="filter.name"
                        >
                            <select
                                v-model="parameters[filter.key]"
                                v-if="filter.type == 'checkbox'"
                            >
                                <option :value="null">Aucun</option>
                                <option
                                    v-for="value in filter.values"
                                    :key="value.key"
                                    :value="value.key"
                                    v-text="value.name"
                                ></option>
                            </select>
                            <input
                                v-else
                                v-model="parameters[filter.key]"
                                :type="filter.type"
                                :placeholder="
                                    filter.placeholder
                                        ? filter.placeholder
                                        : filter.name
                                "
                            />
                        </h-field>
                    </item-list>
                </template>
            </tree-layout>
        </item-list>
        <buttons>
            <button
                @click.prevent="importPappers"
                v-text="'Analyser des données grâce à l\'IA'"
            ></button>
        </buttons>
        <loading :loading="fetchingData" />
    </div>
</template>

<script>
import store from "@/store";
import ApiService from "@/apis/api.service";
import { mapGetters } from "vuex/dist/vuex.cjs.js";

import { FETCH_PROSPECTS } from "@/actions/project/prospect";

export default {
    data() {
        return {
            fetchingData: false,
            parameters: {
                page: 1,
                par_page: 50,
                nombre_proprietaires_min: 1,
            },
            filters: [
                {
                    name: "Page",
                    open: true,
                    filters: [
                        {
                            key: "par_page",
                            name: "Par page",
                            description: "",
                            placeholder: "",
                            type: "checkbox",
                            values: [
                                {
                                    key: "10",
                                    name: "10",
                                },
                                {
                                    key: "20",
                                    name: "20",
                                },
                                {
                                    key: "50",
                                    name: "50",
                                },
                                {
                                    key: "100",
                                    name: "100",
                                },
                            ],
                        },
                        {
                            key: "page",
                            name: "Page",
                            description: "",
                            placeholder: "",
                            type: "checkbox",
                            values: [
                                {
                                    key: "1",
                                    name: "1",
                                },
                                {
                                    key: "2",
                                    name: "2",
                                },
                                {
                                    key: "3",
                                    name: "3",
                                },
                                {
                                    key: "4",
                                    name: "4",
                                },
                                {
                                    key: "5",
                                    name: "5",
                                },
                                {
                                    key: "6",
                                    name: "6",
                                },
                                {
                                    key: "7",
                                    name: "7",
                                },
                                {
                                    key: "8",
                                    name: "8",
                                },
                                {
                                    key: "9",
                                    name: "9",
                                },
                                {
                                    key: "10",
                                    name: "10",
                                },
                            ],
                        },
                    ],
                },
                {
                    name: "Adresse",
                    open: true,
                    filters: [
                        {
                            key: "parcelle_cadastrale",
                            name: "Parcelle cadastrale",
                            description: "",
                            placeholder: "",
                            type: "text",
                        },
                        {
                            key: "code_postal",
                            name: "Code postal",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                        {
                            key: "code_commune",
                            name: "Code commune",
                            description: "",
                            placeholder: "",
                            type: "text",
                        },
                        {
                            key: "region",
                            name: "Région",
                            description: "",
                            placeholder: "",
                            type: "text",
                        },
                        {
                            key: "departement",
                            name: "Département",
                            description: "",
                            placeholder: "",
                            type: "text",
                        },
                    ],
                },
                {
                    name: "Copropriété",
                    open: false,
                    filters: [
                        {
                            key: "type_syndic_copropriete",
                            name: "Type syndicat copropriété",
                            description: "",
                            placeholder: "",
                            type: "checkbox",
                            values: [
                                {
                                    key: "professionnel",
                                    name: "Professionnel",
                                },
                                {
                                    key: "benevole",
                                    name: "Bénévole",
                                },
                            ],
                        },
                        {
                            key: "siret_syndic_professionnel_copropriete",
                            name: "SIRET syndicat professionnel copropriété",
                            description: "",
                            placeholder: "96550338600074",
                            type: "text",
                        },
                        {
                            key: "nom_copropriete",
                            name: "Nom copropriété",
                            description: "",
                            placeholder: "enedis",
                            type: "text",
                        },
                        {
                            key: "nombre_lots_copropriete_min",
                            name: "Nb lots copropriete min",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                        {
                            key: "nombre_lots_copropriete_max",
                            name: "Nb lots copropriete max",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                        {
                            key: "nombre_copropriete_min",
                            name: "Nb copropriete min",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                        {
                            key: "nombre_copropriete_max",
                            name: "Nb copropriete max",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                    ],
                },
                {
                    name: "Propriétaire",
                    open: false,
                    filters: [
                        {
                            key: "siren_proprietaire",
                            name: "SIREN proprietaire",
                            description: "",
                            placeholder: "444608442",
                            type: "text",
                        },
                        {
                            key: "tranche_effectif_proprietaire_min",
                            name: "Tranche effectif proprietaire min",
                            description:
                                "Tranche d'effectifs minimale de l'entreprise, selon la nomenclature Sirene. Note : 00 ou NN donneront les mêmes résultats et veulent dire non employeur",
                            placeholder: "",
                            type: "number",
                        },
                        {
                            key: "tranche_effectif_proprietaire_max",
                            name: "Tranche effectif proprietaire max",
                            description:
                                "Tranche d'effectifs maximale de l'entreprise, selon la nomenclature Sirene. Note : 00 ou NN donneront les mêmes résultats et veulent dire non employeur",
                            placeholder: "",
                            type: "number",
                        },
                        {
                            key: "categorie_juridique_proprietaire",
                            name: "Catégorie juridique propriétaire",
                            description:
                                "Catégorie juridique de l'entreprise, selon la nomenclature Insee.",
                            placeholder: "6540",
                            type: "text",
                        },
                        {
                            key: "code_naf_proprietaire",
                            name: "Code NAF propriétaire",
                            description:
                                "Code NAF de l'entreprise. Il est possible d'indiquer plusieurs codes NAF en les séparant par des virgules.",
                            placeholder: "52.10B",
                            type: "text",
                        },
                        {
                            key: "denomination_proprietaire",
                            name: "Dénomination propriétaire",
                            description: "",
                            placeholder: "enedis",
                            type: "text",
                        },
                        {
                            key: "nombre_proprietaires_min",
                            name: "Nb propriétaires min",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                        {
                            key: "nombre_proprietaires_max",
                            name: "Nb propriétaires max",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                    ],
                },
                {
                    name: "Occupant",
                    open: false,
                    filters: [
                        {
                            key: "siren_occupant",
                            name: "SIREN occupant",
                            description: "",
                            placeholder: "444608442",
                            type: "text",
                        },
                        {
                            key: "tranche_effectif_occupant_min",
                            name: "Tranche effectif occupant min",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                        {
                            key: "tranche_effectif_occupant_max",
                            name: "Tranche effectif occupant max",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                        {
                            key: "categorie_juridique_occupant",
                            name: "Catégorie juridique occupant",
                            description:
                                "Catégorie juridique de l'entreprise, selon la nomenclature Insee.",
                            placeholder: "6540",
                            type: "text",
                        },
                        {
                            key: "code_naf_occupant",
                            name: "Code NAF occupant",
                            description: "",
                            placeholder: "52.10B",
                            type: "text",
                        },
                        {
                            key: "denomination_occupant",
                            name: "Dénomination occupant",
                            description: "",
                            placeholder: "enedis",
                            type: "text",
                        },
                    ],
                },
                {
                    name: "Vente",
                    open: false,
                    filters: [
                        {
                            key: "nature_mutation_vente",
                            name: "Nature mutation vente",
                            description: "Nature de la mutation.",
                            placeholder: "",
                            type: "checkbox",
                            values: [
                                {
                                    key: "vente",
                                    name: "Vente",
                                },
                                {
                                    key: "echange",
                                    name: "Echange",
                                },
                                {
                                    key: "adjudication",
                                    name: "Adjudication",
                                },
                                {
                                    key: "expropriation",
                                    name: "Expropriation",
                                },
                                {
                                    key: "vente_futur_achevement",
                                    name: "Vente futur achèvement",
                                },
                                {
                                    key: "vente_terrain_batir",
                                    name: "Vente terrain à bâtir",
                                },
                            ],
                        },
                        {
                            key: "type_local_vente",
                            name: "Type local vente",
                            description: "",
                            placeholder: "",
                            type: "checkbox",
                            values: [
                                {
                                    key: "appartement",
                                    name: "Appartement",
                                },
                                {
                                    key: "maison",
                                    name: "Maison",
                                },
                                {
                                    key: "dependance",
                                    name: "Dépendance",
                                },
                                {
                                    key: "local_industriel_commercial_ou_assimile",
                                    name: "Local industriel, commercial ou assimilé",
                                },
                            ],
                        },
                        {
                            key: "date_mutation_vente_min",
                            name: "Date mutation vente min",
                            description: "",
                            placeholder: "",
                            type: "date",
                        },
                        {
                            key: "date_mutation_vente_max",
                            name: "Date mutation vente max",
                            description: "",
                            placeholder: "",
                            type: "date",
                        },
                        {
                            key: "prix_vente_min",
                            name: "Prix vente min",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                        {
                            key: "prix_vente_max",
                            name: "Prix vente max",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                        {
                            key: "surface_bati_vente_min",
                            name: "Surface batîment vente min",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                        {
                            key: "surface_bati_vente_max",
                            name: "Surface batîment vente max",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                        {
                            key: "surface_terrain_vente_min",
                            name: "Surface terrain vente min",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                        {
                            key: "surface_terrain_vente_max",
                            name: "Surface terrain vente max",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                        {
                            key: "nombre_pieces_vente_min",
                            name: "Nb pièces vente min",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                        {
                            key: "nombre_pieces_vente_max",
                            name: "Nb pièces vente max",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                    ],
                },
                {
                    name: "Toiture",
                    open: false,
                    filters: [
                        {
                            key: "annee_construction_batiment_min",
                            name: "Année construction batîment min",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                        {
                            key: "annee_construction_batiment_max",
                            name: "Année construction batîment max",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                        {
                            key: "nombre_logements_batiment_min",
                            name: "Nb logements batîment min",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                        {
                            key: "surface_batiment_min",
                            name: "Surface toiture min",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                        {
                            key: "surface_batiment_max",
                            name: "Surface toiture max",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                        {
                            key: "nature_batiment",
                            name: "Nature batîment",
                            description: "",
                            placeholder: "",
                            type: "checkbox",
                            values: [
                                {
                                    key: "indifferenciee",
                                    name: "Indifférenciée",
                                },
                                {
                                    key: "silo",
                                    name: "Silo",
                                },
                                {
                                    key: "industriel_agricole_ou_commercial",
                                    name: "Industriel, agricole ou commercial",
                                },
                                {
                                    key: "monument",
                                    name: "Monument",
                                },
                                {
                                    key: "chapelle",
                                    name: "Chapelle",
                                },
                                {
                                    key: "chateau",
                                    name: "Château",
                                },
                                {
                                    key: "tour_donjon",
                                    name: "Tour, donjon",
                                },
                                {
                                    key: "fort_blockhaus_casemate",
                                    name: "Fort, blockhaus, casemate",
                                },
                                {
                                    key: "eglise",
                                    name: "Eglise",
                                },
                                {
                                    key: "moulin_a_vent",
                                    name: "Moulin à vent",
                                },
                                {
                                    key: "arene_ou_theatre_antique",
                                    name: "Arène ou théâtre antique",
                                },
                                {
                                    key: "arc_de_triomphe",
                                    name: "Arc de triomphe",
                                },
                                {
                                    key: "tribune",
                                    name: "Tribune",
                                },
                            ],
                        },
                        {
                            key: "usage_batiment",
                            name: "Usage batîment",
                            description: "",
                            placeholder: "",
                            type: "checkbox",
                            values: [
                                {
                                    key: "tertiaire_et_autres",
                                    name: "Tertiaire et autres",
                                },
                                {
                                    key: "indifferencie",
                                    name: "Indifférencié",
                                },
                                {
                                    key: "annexe",
                                    name: "Annexe",
                                },
                                {
                                    key: "residentiel",
                                    name: "Résidentiel",
                                },
                                {
                                    key: "entrepot",
                                    name: "Entrepôt",
                                },
                                {
                                    key: "residentiel_individuel",
                                    name: "Résidentiel individuel",
                                },
                                {
                                    key: "residentiel_collectif",
                                    name: "Résidentiel collectif",
                                },
                                {
                                    key: "commercial_et_services",
                                    name: "Commercial et services",
                                },
                                {
                                    key: "agricole",
                                    name: "Agricole",
                                },
                                {
                                    key: "industriel",
                                    name: "Industriel",
                                },
                                {
                                    key: "sportif",
                                    name: "Sportif",
                                },
                                {
                                    key: "religieux",
                                    name: "Religieux",
                                },
                                {
                                    key: "dependance",
                                    name: "Dépendance",
                                },
                                {
                                    key: "secondaire",
                                    name: "Secondaire",
                                },
                            ],
                        },
                    ],
                },
                {
                    name: "DPE",
                    open: false,
                    filters: [
                        {
                            key: "classe_bilan_dpe",
                            name: "Classe bilan DPE",
                            description: "",
                            placeholder: "",
                            type: "checkbox",
                            values: [
                                {
                                    key: "A",
                                    name: "A",
                                },
                                {
                                    key: "B",
                                    name: "B",
                                },
                                {
                                    key: "C",
                                    name: "C",
                                },
                                {
                                    key: "D",
                                    name: "D",
                                },
                                {
                                    key: "E",
                                    name: "E",
                                },
                                {
                                    key: "F",
                                    name: "F",
                                },
                            ],
                        },
                        {
                            key: "type_installation_chauffage_dpe",
                            name: "Type installation chauffage DPE",
                            description: "",
                            placeholder: "",
                            type: "checkbox",
                            values: [
                                {
                                    key: "individuel",
                                    name: "Individuel",
                                },
                                {
                                    key: "collectif",
                                    name: "Collectif",
                                },
                            ],
                        },
                        {
                            key: "type_energie_chauffage_dpe",
                            name: "Type énergie chauffage DPE",
                            description: "",
                            placeholder: "",
                            type: "checkbox",
                            values: [
                                {
                                    key: "gaz",
                                    name: "Gaz",
                                },
                                {
                                    key: "electricite",
                                    name: "Electricité",
                                },
                                {
                                    key: "fioul",
                                    name: "Fioul",
                                },
                                {
                                    key: "bois",
                                    name: "Bois",
                                },
                                {
                                    key: "gpl_butane_propane",
                                    name: "GPL butane propane",
                                },
                                {
                                    key: "solaire",
                                    name: "Solaire",
                                },
                                {
                                    key: "charbon",
                                    name: "Charbon",
                                },
                                {
                                    key: "reseau_de_chaleur",
                                    name: "Réseau de chaleur",
                                },
                            ],
                        },
                    ],
                },
                {
                    name: "Fonds de commerce",
                    open: false,
                    filters: [
                        {
                            key: "prix_fonds_de_commerce_min",
                            name: "Prix fonds de commerce min",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                        {
                            key: "prix_fonds_de_commerce_max",
                            name: "Prix fonds de commerce max",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                        {
                            key: "date_fonds_de_commerce_min",
                            name: "Date fonds de commerce min",
                            description: "",
                            placeholder: "",
                            type: "date",
                        },
                        {
                            key: "date_fonds_de_commerce_max",
                            name: "Date fonds de commerce max",
                            description: "",
                            placeholder: "",
                            type: "date",
                        },
                        {
                            key: "code_naf_fonds_de_commerce",
                            name: "Code NAF fonds de commerce",
                            description: "",
                            placeholder: "52.10B",
                            type: "text",
                        },
                    ],
                },
                {
                    name: "Permis",
                    open: false,
                    filters: [
                        {
                            key: "statut_permis",
                            name: "Statut permis",
                            description: "",
                            placeholder: "",
                            type: "checkbox",
                            values: [
                                {
                                    key: "autorise",
                                    name: "Autorisé",
                                },
                                {
                                    key: "commence",
                                    name: "Commencé",
                                },
                                {
                                    key: "termine",
                                    name: "Terminé",
                                },
                                {
                                    key: "annule",
                                    name: "Annulé",
                                },
                            ],
                        },
                        {
                            key: "date_autorisation_permis_min",
                            name: "Date autorisation permis min",
                            description: "",
                            placeholder: "",
                            type: "date",
                        },
                        {
                            key: "date_autorisation_permis_max",
                            name: "Date autorisation permis max",
                            description: "",
                            placeholder: "",
                            type: "date",
                        },
                        {
                            key: "nombre_permis_min",
                            name: "Nb permis min",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                        {
                            key: "nombre_permis_max",
                            name: "Nombre permis max",
                            description: "",
                            placeholder: "",
                            type: "number",
                        },
                    ],
                },
            ],
        };
    },

    methods: {
        async importPappers() {
            const parameters = Object.fromEntries(
                Object.entries(this.parameters).filter(([k, v]) => v)
            );

            this.fetchingData = true;

            try {
                const { data } = await ApiService.post(
                    `project/${this.project.slug}/api/pappers`,
                    parameters
                );

                store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.fetchingData = false;
            }
        },
    },

    computed: {
        ...mapGetters(["project"]),

        allFilters() {
            return this.filters.reduce((acc, group) => {
                return acc.concat(group.filters);
            }, []);
        },
    },
};
</script>
