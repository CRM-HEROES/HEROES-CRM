<template>
    <modal :name="name" title="Pipedrive" @open="showSetting">
        <form
            class="hc-flex-column"
            style="height: 100%"
            @submit.prevent="registerPipedrive"
        >
            <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                <v-field label="Nom du compte" required v-slot="{ label }"
                    ><input
                        :placeholder="label + ' ...'"
                        v-model="setting.name"
                        required
                /></v-field>
                <v-field
                    label="Token"
                    required
                    v-slot="{ label }"
                    v-tuto="{
                        key: 'project.settings.pipedrive',
                        name: 'Pipedrive',
                        body:
                            'Sur votre compte pipedrive:<br>' +
                            'Cliquer sur votre menu Profil en haut à droite<br>' +
                            'Aller dans préférence personnelle<br>' +
                            'Et cliquer sur l\'onglet API',
                    }"
                    ><input
                        :placeholder="label + ' ...'"
                        v-model="setting.token"
                        required
                /></v-field>
            </item-list>
            <buttons>
                <button
                    v-if="setting && setting.id"
                    @click.prevent="remove"
                    class="hc-button-danger"
                    v-text="$t('delete')"
                ></button>
                <button v-text="$t('update')"></button>
            </buttons>
            <loading
                :loading="updatingSetting || showingSetting || removingSetting"
                :title="loadingTitle"
            />
        </form>
    </modal>
</template>

<script>
import store from "@/store";
import ApiService from "@/apis/api.service";
import { mapGetters } from "vuex";

// Actions
import { CLOSE_MODAL } from "@/actions/modal";
import { FETCH_PROSPECTS } from "@/actions/project/prospect";

export default {
    data() {
        return {
            name: "setting-pipedrive",
            updatingSetting: false,
            showingSetting: false,
            removingSetting: false,
            loadingTitle: null,
            setting: {
                name: "",
                token: "",
            },
        };
    },

    methods: {
        /**
         *
         */
        async showSetting() {
            this.showingSetting = true;

            try {
                const { data } = await ApiService.get(
                    `project/${this.project.slug}/pipedrive`
                );

                if (data) {
                    this.setting = data;
                }
            } finally {
                this.showingSetting = false;
            }
        },

        /**
         *
         */
        async registerPipedrive() {
            this.update().then(() => {
                this.createWebhooks().then(() => {
                    const importProspects = () => {
                        hcConfirm(
                            "Voulez-vous importer vos prospects HeroesCRM vers votre compte Pipedrive ?",
                            () => {
                                this.importProspects();
                                store.commit(CLOSE_MODAL);
                            },
                            () => {
                                store.commit(CLOSE_MODAL);
                            }
                        );
                    };

                    hcConfirm(
                        "Voulez-vous récupérer les prospects depuis Pipedrive ?",
                        () => {
                            this.getPersons().then(() => {
                                importProspects();
                            });
                        },
                        () => {
                            importProspects();
                        }
                    );
                });
            });
        },

        /**
         *
         */
        async update() {
            this.loadingTitle = "Enregistrement du token ...";
            this.updatingSetting = true;

            try {
                const { data } = await ApiService.post(
                    `project/${this.project.slug}/pipedrive`,
                    this.setting
                );
                this.setting = data;
            } finally {
                this.updatingSetting = false;
            }
        },

        /**
         *
         */
        async getPersons() {
            this.loadingTitle =
                "Récupération des prospects depuis Pipedrive ...";
            this.updatingSetting = true;

            try {
                await ApiService.get(
                    `project/${this.project.slug}/pipedrive/${this.setting.id}/person`
                );
                store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.updatingSetting = false;
            }
        },

        /**
         *
         */
        async createWebhooks() {
            this.loadingTitle =
                "Création de Webhooks d'ajout et modification ...";
            this.updatingSetting = true;

            try {
                await ApiService.post(
                    `project/${this.project.slug}/pipedrive/${this.setting.id}/webhook`
                );
            } finally {
                this.updatingSetting = false;
            }
        },

        /**
         *
         */
        async importProspects() {
            try {
                await ApiService.post(
                    `project/${this.project.slug}/pipedrive/${this.setting.id}/import`
                );

                flashInfo({
                    title: "Pipedrive",
                    body: "Vos prospects sont en cours d'import vers votre compte Pipedrive",
                    duration: 5000,
                });
            } finally {
            }
        },

        async remove() {
            this.loadingTitle = null;
            this.removingSetting = true;

            try {
                await ApiService.delete(
                    `project/${this.project.slug}/pipedrive/${this.setting.id}`
                );
                this.setting = {
                    name: "",
                    token: "",
                };
            } finally {
                this.removingSetting = false;
                store.commit(CLOSE_MODAL);
            }
        },
    },

    computed: {
        ...mapGetters(["project"]),
    },
};
</script>
