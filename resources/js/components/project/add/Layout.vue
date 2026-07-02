<template>
    <form
        class="hc-flex-column"
        style="max-height: 400px"
        @submit.prevent="storeProject"
    >
        <item-list gap="5px">
            <v-field :label="$t('name')" required v-slot="{ label }"
                ><input
                    required
                    type="text"
                    :placeholder="label + ' ...'"
                    v-model="project.name"
            /></v-field>
            <v-field :label="'Type de métier associé à votre projet'" required>
                <select v-model="project.job_category">
                    <option
                        value="commercial_prospecting_agency"
                        v-text="'Agence commerciale de prospection'"
                    ></option>
                    <option
                        value="solar_panel_installer"
                        v-text="'Installateur de panneaux photovoltaïques'"
                    ></option>
                    <option
                        value="global_renovation_installer"
                        v-text="'Installateur de rénovation globale'"
                    ></option>
                    <option
                        value="heat_pump_installer"
                        v-text="'Installateur de pompes à chaleur'"
                    ></option>
                    <option
                        value="passenger_transport"
                        v-text="'Transport de personnes'"
                    ></option>
                    <option
                        value="field_intervention_manager"
                        v-text="'Gestionnaire d\'intervention terrain'"
                    ></option>
                    <option
                        value="exhibition_manager"
                        v-text="'Exposant salons et expositions'"
                    ></option>
                    <option
                        value="vehicle_geolocation"
                        v-text="'Géolocalisation de véhicules'"
                    ></option>
                    <option value="other" v-text="'Autre'"></option>
                </select>
            </v-field>

            <template v-if="showMore">
                <v-field :label="$t('phone')" v-slot="{ label }"
                    ><input
                        type="text"
                        :placeholder="label + ' ...'"
                        v-model="project.phone_number"
                /></v-field>
                <v-field :label="$t('email')" v-slot="{ label }"
                    ><input
                        type="text"
                        :placeholder="label + ' ...'"
                        v-model="project.email"
                /></v-field>
                <v-field :label="$t('street')" v-slot="{ label }"
                    ><input
                        type="text"
                        :placeholder="label + ' ...'"
                        v-model="project.street"
                /></v-field>
                <v-field :label="$t('street_bis')" v-slot="{ label }"
                    ><input
                        type="text"
                        :placeholder="label + ' ...'"
                        v-model="project.street_bis"
                /></v-field>
                <v-field :label="$t('postal_code')" v-slot="{ label }"
                    ><input
                        type="text"
                        :placeholder="label + ' ...'"
                        v-model="project.postal_code"
                /></v-field>
                <v-field :label="$t('city')" v-slot="{ label }"
                    ><input
                        type="text"
                        :placeholder="label + ' ...'"
                        v-model="project.city"
                /></v-field>
                <v-field :label="$t('country')" v-slot="{ label }"
                    ><input
                        type="text"
                        :placeholder="label + ' ...'"
                        v-model="project.country"
                /></v-field>
                <v-field label="Num TVA Intra" v-slot="{ label }"
                    ><input
                        type="text"
                        :placeholder="label + ' ...'"
                        v-model="project.num_tva_intra"
                /></v-field>
                <v-field label="NAF" v-slot="{ label }"
                    ><input
                        type="text"
                        :placeholder="label + ' ...'"
                        v-model="project.naf"
                /></v-field>
                <v-field label="SIRET" v-slot="{ label }"
                    ><input
                        type="number"
                        :placeholder="label + ' ...'"
                        v-model="project.siret"
                /></v-field>
                <v-field label="Capital" v-slot="{ label }"
                    ><input
                        type="text"
                        :placeholder="label + ' ...'"
                        v-model="project.capital"
                /></v-field>
            </template>
            <item tag="a" @click.prevent="showMore = !showMore">
                <div
                    class="hc-item-main-content"
                    v-text="showMore ? 'Afficher moins' : 'Afficher plus'"
                ></div>
                <icon
                    :class="showMore ? 'fa fa-caret-up' : 'fa fa-caret-down'"
                />
            </item>
        </item-list>
        <buttons>
            <button v-text="$t('add')"></button>
        </buttons>
        <loading :loading="addingProject" />
    </form>
</template>

<script>
import store from "@/store";

// Actions
import { ADD_PROJECT } from "@/actions/project";
import { CLOSE_MODAL } from "@/actions/modal";

export default {
    data() {
        return {
            project: this.newProject(),
            addingProject: false,
            showMore: false,
        };
    },

    methods: {
        /**
         *
         */
        newProject() {
            return {
                name: "",
                phone_number: "",
                email: "",
                street: "",
                street_bis: "",
                postal_code: "",
                city: "",
                country: "",
                num_tva_intra: "",
                naf: "",
                siret: "",
                capital: "",
            };
        },

        /**
         *
         */
        async storeProject() {
            this.addingProject = true;

            try {
                const project = await store.dispatch(ADD_PROJECT, this.project);
                // show the project
                this.$router.push({
                    name: "prospect",
                    params: {
                        project: project.slug,
                    },
                });
            } finally {
                this.addingProject = false;
                this.project = this.newProject();
                store.commit(CLOSE_MODAL);
            }
        },
    },
};
</script>
