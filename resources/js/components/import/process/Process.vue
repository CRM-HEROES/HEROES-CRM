<template>
    <item-list style="height: 100%; overflow: auto" padding="12px" gap="2px">
        <item
            id="hc-import-process-process"
            v-if="!prospectImport.is_processing"
            @click="processImport"
            style="color: white !important; background-color: #12a0f3"
        >
            <icon class="fa fa-play" style="color: white" />
            <div
                class="hc-item-main-content"
                v-text="
                    prospectImport.processed_at
                        ? $t('import.process.tab.import.re_process')
                        : $t('import.process.tab.import.process')
                "
            ></div>
            <loading :loading="processingImport" />
        </item>

        <item
            v-if="prospectImport.is_processing"
            @click="stop"
            style="color: white !important; background-color: #d9402c"
        >
            <icon class="fa fa-times" style="color: white" />
            <div
                class="hc-item-main-content"
                v-text="$t('import.process.tab.import.stop')"
            ></div>
            <loading :loading="stoppingImport" />
        </item>

        <item @click="deleteImport">
            <icon class="fa fa-trash" style="color: #d9402c" />
            <div
                class="hc-item-main-content"
                v-text="$t('import.process.tab.import.delete.import')"
            ></div>
            <loading :loading="deletingImport" />
        </item>

        <item v-if="prospectImport.processed_at" @click="deleteProspects">
            <icon class="fa fa-trash" style="color: #d9402c" />
            <div
                class="hc-item-main-content"
                v-text="$t('import.process.tab.import.delete.prospects')"
            ></div>
            <loading :loading="deletingProspects" />
        </item>

        <item v-if="prospectImport.processed_at" @click="deleteImportProspects">
            <icon class="fa fa-trash" style="color: #d9402c" />
            <div
                class="hc-item-main-content"
                v-text="
                    $t('import.process.tab.import.delete.import_and_prospects')
                "
            ></div>
            <loading :loading="deletingImportProspects" />
        </item>

        <item v-if="prospectImport.source == 'file'" @click="download">
            <icon class="fa fa-file-download" />
            <div
                class="hc-item-main-content"
                v-text="$t('import.process.tab.import.download')"
            ></div>
        </item>

        <item>
            <icon class="fa fa-eye" />
            <div
                class="hc-item-main-content"
                v-text="$t('import.process.tab.import.view_prospects')"
            ></div>
        </item>

        <item
            id="hc-import-process-prospects-table-setting"
            v-if="prospectImport.mapping"
            @click="setProspectsTableSetting"
        >
            <icon class="fa fa-columns" />
            <div
                class="hc-item-main-content hc-flex-column"
                style="padding: 5px 0"
            >
                <span
                    v-text="
                        $t('import.process.tab.import.prospects_table.title')
                    "
                    style="color: #333333"
                ></span>
                <span
                    style="white-space: normal; color: #777777"
                    v-text="
                        $t('import.process.tab.import.prospects_table.body')
                    "
                ></span>
            </div>
            <loading :loading="settingProspectsTable" />
        </item>

        <template v-if="prospectImport.source == 'webservice'">
            <item class="hc-import-webservice-step">
                <icon class="fa fa-info" />
                <div
                    class="hc-item-main-content"
                    v-html="$t('import.process.tab.import.webservice.step_1')"
                ></div>
            </item>
            <item class="hc-import-webservice-step">
                <icon class="fa fa-step-forward" />
                <div
                    class="hc-item-main-content"
                    v-html="$t('import.process.tab.import.webservice.step_2')"
                ></div>
            </item>
            <item class="hc-import-webservice-step">
                <icon class="fa fa-step-forward" />
                <div
                    class="hc-item-main-content"
                    v-html="$t('import.process.tab.import.webservice.step_3')"
                ></div>
            </item>
            <item class="hc-import-webservice-step">
                <icon class="fa fa-file" />
                <div
                    class="hc-item-main-content"
                    v-html="$t('import.process.tab.import.webservice.step_4')"
                ></div>
            </item>
            <item class="hc-import-webservice-step">
                <icon class="fa fa-file" />
                <div
                    class="hc-item-main-content"
                    v-html="$t('import.process.tab.import.webservice.step_5')"
                ></div>
            </item>
            <item class="hc-import-webservice-step">
                <icon class="fa fa-external-link" />
                <div
                    class="hc-item-main-content"
                    v-html="$t('import.process.tab.import.webservice.step_6')"
                ></div>
            </item>
            <item class="hc-import-webservice-step">
                <icon class="fa fa-external-link" />
                <div class="hc-item-main-content">
                    <span
                        class="hc-item-main-content"
                        v-html="
                            $t('import.process.tab.import.webservice.step_7')
                        "
                    ></span
                    ><br />
                    <b
                        v-text="webhookURL"
                        @click.stop="copyWebhookURLToClipboard"
                        style="cursor: pointer"
                    ></b>
                </div>
            </item>
            <item class="hc-import-webservice-step">
                <icon class="fa fa-external-link" />
                <div
                    class="hc-item-main-content"
                    v-html="$t('import.process.tab.import.webservice.step_8')"
                ></div>
            </item>
            <mapping-column-field-copy-row
                v-for="header in filteredHeaders"
                :key="header.index"
                :column="header.header"
                :mapping="getMapping(header.index)"
                @click="selectColumn(header.header)"
            />
        </template>
    </item-list>
</template>

<style>
.hc-import-webservice-step {
    padding-top: 5px !important;
    padding-bottom: 5px !important;
}

.hc-import-webservice-step .hc-item-main-content {
    white-space: initial;
}
</style>

<script>
import ApiService from "@/apis/api.service";
import ImportService from "@/apis/project/import";
import { mapGetters } from "vuex";
import store from "@/store";
import { API_URL } from "@/apis/common";

import { GET_PROJECT_USER_SETTING } from "@/actions/project/user/setting";

import {
    UPDATE_IMPORT,
    REMOVE_IMPORT,
    SET_IMPORT,
    SHOW_IMPORT,
} from "@/actions/project/import";
import { FETCH_PROSPECTS } from "@/actions/project/prospect";
import { UPDATE_PROJECT_USER_SETTING } from "@/actions/project/user/setting";

import { SHOW_PROJECT } from "@/actions/project";
import MappingColumnFieldCopyRow from "./process/MappingColumnFieldCopyRow.vue";

export default {
    components: {
        MappingColumnFieldCopyRow,
    },

    props: {
        process: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            tab: 0,
            columnKeyword: "",
            processingImport: false,
            stoppingImport: false,
            deletingImport: false,
            deletingImportProspects: false,
            deletingProspects: false,
            settingProspectsTable: false,
        };
    },

    methods: {
        // LAUNCH AND STOP IMPORT

        /**
         * Launch import
         */
        async processImport() {
            this.processingImport = true;

            try {
                // Launch import
                await store.dispatch(UPDATE_IMPORT, {
                    id: this.prospectImport.id,
                    is_processing: true,
                });
                this.prospectImport.is_processing = true;

                // If prospects table setting is not configured for the current user
                // use the import columns as the default columns for the prospects table
                this.getColumns();
                // Update prospects table
                this.refreshProspectsList();

                // Flash: "Import is processing"
                flashInfo({
                    title: "Import",
                    body: this.$t("import.process.tab.import.processing"),
                    duration: 5000,
                });
            } finally {
                this.processingImport = false;
            }

            // Tuto:
            tuto(
                document.getElementById(
                    "hc-import-process-prospects-table-setting"
                ),
                "Utilisez les colonnes de votre import comme colonnes dans votre tableau de prospects.",
                "import.process.prospects-table-setting"
            );
        },

        /**
         * Stop importing
         */
        async stop() {
            this.stoppingImport = true;

            try {
                await store.dispatch(UPDATE_IMPORT, {
                    id: this.prospectImport.id,
                    is_processing: false,
                });
            } finally {
                this.prospectImport.is_processing = false;
                this.stoppingImport = false;
            }
        },

        // DELETE IMPORT AND PROSPECTS

        /**
         *
         * @param {*} deleteImport
         * @param {*} deleteProspects
         */
        async deleteImportAndProspects(deleteImport, deleteProspects) {
            await store.dispatch(REMOVE_IMPORT, {
                slug: this.prospectImport.id,
                params: {
                    import: deleteImport,
                    prospects: deleteProspects,
                },
            });
        },

        /**
         * Delete only the import
         * (without deleting the prospects inside the import)
         */
        async deleteImport() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.deletingImport = true;

                this.deleteImportAndProspects(true, false).finally(() => {
                    this.deletingImport = false;
                    store.commit(SET_IMPORT, null);
                });
            });
        },

        /**
         * Delete only the prospect inside the import
         */
        async deleteProspects() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.deletingProspects = true;

                this.deleteImportAndProspects(false, true)
                    .then(() => {
                        store.dispatch(FETCH_PROSPECTS);
                    })
                    .finally(() => {
                        this.deletingProspects = false;
                    });
            });
        },

        /**
         * Delete the import
         * and the prospect inside the import
         */
        async deleteImportProspects() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.deletingImportProspects = true;

                this.deleteImportAndProspects(true, true)
                    .then(() => {
                        store.dispatch(FETCH_PROSPECTS);
                    })
                    .finally(() => {
                        this.deletingImportProspects = false;
                        store.commit(SET_IMPORT, null);
                    });
            });
        },

        // DOWNLOAD IMPORT FILE

        /**
         * Download import file
         */
        download() {
            location.href = `${API_URL}/project/${this.project.slug}/import/${this.prospectImport.id}/download`;
        },

        // PROSPECTS TABLE

        /**
         * Define the import columns
         * as the prospects table columns
         */
        async setProspectsTableSetting() {
            this.settingProspectsTable = true;

            try {
                // Get import mapping
                await store.dispatch(SHOW_IMPORT, this.prospectImport.id);
                const settings = this.prospectImport.mapping
                    .filter((s) => s)
                    .map((s) => ({
                        key: s,
                    }));

                // Update the prospects table columns setting
                await store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                    key: "prospects-table",
                    value: settings,
                });

                // Refresh project and prospects table
                store.dispatch(SHOW_PROJECT, this.project.slug);
                store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.settingProspectsTable = false;
            }
        },

        /**
         * When importing prospects
         * Refresh the prospects table
         * every 5s
         */
        refreshProspectsList() {
            const intervalTime = 5000;
            let interval;

            interval = setInterval(async () => {
                try {
                    // Check if import has been finished
                    const { data } = await ImportService.show(
                        this.project.slug,
                        this.prospectImport.id
                    );

                    store.dispatch(FETCH_PROSPECTS);

                    // If import is finished
                    // stop refreshing the prospects table
                    if (!data.is_processing) {
                        clearInterval(interval);
                    }
                } catch (e) {
                    clearInterval(interval);
                }
            }, intervalTime);

            store.dispatch(FETCH_PROSPECTS);
        },

        /**
         * Get prospects table columns setting
         * For the current user
         */
        async getColumns() {
            try {
                await store.dispatch(GET_PROJECT_USER_SETTING, {
                    key: "prospects-table",
                });

                // if it is empty
                // use the import columns
                // as the default prospects table columns
                // for the current user
                if (this.projectUserSettingsProspectsTable.length == 0) {
                    this.setProspectsTableSetting();
                }
            } finally {
            }
        },

        // MAPPING

        /**
         * Get mapping column name
         * @param {*} category
         */
        getMapping(index) {
            if (!this.prospectImport.mapping) {
                return null;
            }

            const key = this.prospectImport.mapping[index];
            if (!key) {
                return null;
            }

            // Event
            if (key == "events") {
                return "RDV";
                // Category
            } else if (key.indexOf("category->") == 0) {
                const id = key.substring(10);
                const category = this.categories.find((c) => c.id == id);

                if (category) {
                    return category.name;
                }
                // Thread
            } else if (key.indexOf("thread->") == 0) {
                const id = key.substring(8);
                const thread = this.threads.find((t) => t.id == id);

                if (thread) {
                    return thread.name;
                }
                // Meta field
            } else if (key.indexOf("meta->") == 0) {
                const slug = key.substring(6);
                const field = this.fields.find(
                    (f) => f.meta && f.for == "prospect" && f.slug == slug
                );

                if (field) {
                    return field.name;
                }
                // Default field
            } else {
                const slug = key;
                const field = this.fields.find(
                    (f) => !f.meta && f.for == "prospect" && f.slug == slug
                );

                if (field) {
                    return field.name;
                }
            }

            return null;
        },

        // WEB SERVICE IMPORT

        /**
         * Copy webhook URL to clipboard
         */
        copyWebhookURLToClipboard() {
            navigator.clipboard.writeText(this.webhookURL);

            flashInfo({
                title: "Import",
                body: this.$t(
                    "import.process.tab.import.webservice.webhook_url_copied"
                ),
                duration: 5000,
            });
        },

        /**
         * For webservice column,
         * copy header param into clipboard
         * when clicking the row
         * @param {*} header
         */
        selectColumn(header) {
            navigator.clipboard.writeText(header);

            flashInfo({
                title: "Import",
                body: this.$t(
                    "import.process.tab.import.webservice.query_string_copied"
                ),
                duration: 5000,
            });
        },
    },

    watch: {
        process() {
            if (this.process) {
                this.processImport();
            }
        },
    },

    mounted() {
        // If the import was already processing
        // (eg. the page was refreshed mid-import)
        // resume polling the prospects table
        if (this.prospectImport && this.prospectImport.is_processing) {
            this.refreshProspectsList();
        }
    },

    computed: {
        ...mapGetters([
            "projectUserSettingsProspectsTable",
            "project",
            "prospectImport",
            "categories",
            "threads",
            "fields",
        ]),

        /**
         * List of import columns
         */
        filteredHeaders() {
            if (
                !this.prospectImport ||
                !Array.isArray(this.prospectImport.headers)
            ) {
                return [];
            }

            const keyword = removeStringAccent(this.columnKeyword);

            return this.prospectImport.headers
                .map((header, index) => ({ header, index }))
                .filter(
                    ({ header }) =>
                        removeStringAccent(header).indexOf(keyword) >= 0
                );
        },

        /**
         * Webhook URL
         * for web service import
         */
        webhookURL: function () {
            return (
                window.location.origin +
                "/api/webservice/" +
                this.prospectImport.id +
                "/prospect?token=" +
                this.prospectImport.token
            );
        },
    },
};
</script>
