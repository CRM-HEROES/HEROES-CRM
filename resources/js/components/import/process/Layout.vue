<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <!-- List of imports -->
        <template #1>
            <div class="hc-flex-column" style="height: 100%">
                <search v-model="importKeyword" />
                <item-list class="hc-flex-1" padding="5px">
                    <import-row
                        v-for="c in filteredImports"
                        :key="c.id"
                        :prospect-import="c"
                        @click="setImport(c)"
                    />
                </item-list>
                <buttons v-if="can('all.prospect.import')">
                    <a @click.prevent="addImport" v-text="$t('add')"></a>
                </buttons>
            </div>
        </template>

        <template #2 v-if="prospectImport">
            <div class="hc-flex-column" style="height: 100%">
                <!-- Current import name -->
                <item @click="setImport(null)" class="bordered">
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content"
                        v-text="prospectImport.name"
                    ></div>
                    <icon
                        tag="a"
                        class="fa fa-cog"
                        @click.prevent.stop="edit"
                    />
                </item>

                <!-- Import tab -->
                <tab
                    :count="4"
                    :tab="importTab"
                    @tab:change="(tab) => (importTab = tab)"
                >
                    <template #1>{{
                        $t("import.process.tab.mapping.title")
                    }}</template>
                    <template #2>{{
                        $t("import.process.tab.relations.title")
                    }}</template>
                    <template #3 v-if="prospectImport.source == 'file'">{{
                        $t("import.process.tab.duplicate.title")
                    }}</template>
                    <template #4>{{
                        $t("import.process.tab.import.title")
                    }}</template>
                </tab>

                <tab-layout :count="4" :tab="importTab" class="hc-flex-1">
                    <template #1>
                        <mapping />
                    </template>
                    <template #2>
                        <relation />
                    </template>
                    <template #3 v-if="prospectImport.source == 'file'">
                        <duplicate />
                    </template>
                    <template #4>
                        <process
                            :process="process"
                            @processed="process = false"
                        />
                    </template>
                </tab-layout>

                <!-- Prev / Next -->
                <buttons>
                    <button
                        :disabled="importTab <= 0"
                        @click.prevent="importTab--"
                    >
                        {{ $t("previous") }}
                    </button>
                    <button
                        :disabled="importTab >= 4"
                        id="hc-import-process-next"
                        @click.prevent="
                            importTab == 3 ? processImport() : importTab++
                        "
                    >
                        {{ $t("next") }}
                    </button>
                </buttons>
            </div>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_IMPORTS, SET_IMPORT } from "@/actions/project/import";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import ImportRow from "./ImportRow.vue";
import Mapping from "./Mapping.vue";
import Relation from "./Relation.vue";
import Process from "./Process.vue";
import Duplicate from "./Duplicate.vue";

export default {
    components: {
        ImportRow,
        Mapping,
        Relation,
        Process,
        Duplicate,
    },

    data() {
        return {
            importKeyword: "",

            tab: 0,
            importTab: 0,
            process: false,
        };
    },

    created() {
        this.fetchImports();
    },

    methods: {
        /**
         *
         */
        addImport() {
            store.commit(OPEN_MODAL, "import-add");
        },

        /**
         *
         */
        fetchImports() {
            store.dispatch(FETCH_IMPORTS);
        },

        /**
         *
         * @param {*} prospectImport
         */
        async setImport(pi) {
            this.importTab = 0;
            store.commit(SET_IMPORT, pi);
            if (!pi) {
                return;
            }
            // store.commit(SET_IMPORT, prospectImport);
        },

        /**
         *
         */
        processImport() {
            hcConfirm(
                "Etes-vous sûr de vouloir effectuer l'import ?",
                async () => {
                    this.process = true;
                }
            );
        },

        edit() {
            store.commit(OPEN_MODAL, "import-update");
            store.commit(SET_IMPORT, this.prospectImport);
        },
    },

    watch: {
        async prospectImport(newValue) {
            if (newValue) {
                this.tab = 1;
            } else {
                this.tab = 0;
            }
        },

        async tab() {
            if (this.tab == 1 && this.importTab == 0) {
                await this.$nextTick();

                tuto(
                    document.getElementById(
                        "hc-import-process-mapping-columns"
                    ),
                    "En cliquant sur chaque colonne dans cette liste, vous pouvez associer la colonne à un champ/catégorie de filtres dans votre projet.",
                    "import.process.mapping.columns",
                    "Import - Mapper les colonnes",
                    500
                );

                tuto(
                    document.getElementById("hc-import-process-next"),
                    "Quand vous avez fini de mapper chacune de votre colonne au champ de votre projet, vous pouvez passer à l'étape suivante.",
                    "import.process.next.relations",
                    "Import - Mapper les colonnes - Suivant"
                );
            }
        },

        async importTab() {
            if (this.importTab == 1) {
                await this.$nextTick();

                tuto(
                    document.getElementById("hc-import-process-relations"),
                    "Associez tous les prospects de cet import à certains utilisateurs ou groupes ou des filtres.",
                    "import.process.relations",
                    "Import - Associer les prospects à des utilisateurs/groupes/filtres",
                    500
                );

                tuto(
                    document.getElementById("hc-import-process-next"),
                    "Sinon, vous pouvez passer à l'étape suivante.",
                    "import.process.next.process",
                    "Import - Associer les prospects à des utilisateurs/groupes/filtres - Suivant"
                );
            } else if (this.importTab == 2) {
                await this.$nextTick();

                tuto(
                    document.getElementById("hc-import-process-process"),
                    "Si tout est OK pour vous, il vous suffit de cliquer ici pour effectuer l'import.<br>Quand votre import sera terminé, nous vous enverrons une notification par email.",
                    "import.process.process",
                    "Import - Valider un import",
                    500
                );
            }
        },
    },

    computed: {
        ...mapGetters(["imports", "prospectImport", "can"]),

        /**
         *
         */
        filteredImports() {
            const keyword = removeStringAccent(this.importKeyword);

            return this.imports
                .filter(
                    (prospectImport) =>
                        removeStringAccent(prospectImport.name).indexOf(
                            keyword
                        ) >= 0
                )
                .sort((i1, i2) => (i1.id < i2.id ? 1 : -1));
        },
    },
};
</script>
