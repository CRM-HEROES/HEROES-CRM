<template>
    <item-list style="height: 100%; overflow: auto" padding="12px" gap="2px">
        <!-- Notifier SMS de bienvenue -->
        <item
            v-if="!prospectImport.is_processing"
            tag="label"
            class="hc-import-notify-welcome-sms"
            style="cursor: pointer"
        >
            <input
                type="checkbox"
                v-model="notifyWelcomeSms"
                @change="onToggleNotifyWelcomeSms"
            />
            <div
                class="hc-item-main-content"
                v-text="$t('import.process.tab.import.notify_welcome_sms')"
            ></div>
            <loading :loading="checkingSmsSourceSetting" />
        </item>

        <item-list
            v-if="notifyWelcomeSms && !prospectImport.is_processing"
            padding="0 12px 10px 12px"
            gap="5px"
        >
            <v-field
                :label="$t('import.process.tab.import.welcome_sms_source')"
                v-slot="{ label }"
            >
                <select v-model="welcomeSmsSource" @change="onChangeWelcomeSmsSource">
                    <option value="brevo">{{ $t("prospect.sms.via_brevo") }}</option>
                    <option value="smsbox">{{ $t("prospect.sms.via_smsbox") }}</option>
                    <option value="ultramsg">{{ $t("prospect.sms.via_ultramsg") }}</option>
                    <option value="mtarget">{{ $t("prospect.sms.via_mtarget") }}</option>
                </select>
            </v-field>

            <v-field
                :label="$t('import.process.tab.import.welcome_sms_message')"
                required
                v-slot="{ label }"
            >
                <textarea
                    :placeholder="label + ' ...'"
                    v-model="welcomeSmsMessage"
                    @change="saveWelcomeSmsSettings"
                    required
                    rows="3"
                ></textarea>
            </v-field>
        </item-list>

        <item
            v-if="!prospectImport.is_processing && (!prospectImport.roles || prospectImport.roles.length === 0)"
            style="color: #92400e !important; background-color: #fef3c7"
        >
            <icon class="fa fa-exclamation-triangle" style="color: #92400e" />
            <div
                class="hc-item-main-content"
                style="white-space: normal"
                v-text="'Aucun rôle sélectionné dans l\'étape Relations — les prospects importés ne seront assignés à personne automatiquement.'"
            ></div>
        </item>
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

        <item
            v-if="['file', 'google_sheets'].includes(prospectImport.source)"
            @click="download"
        >
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
    FETCH_IMPORTS,
    UPDATE_IMPORT,
    REMOVE_IMPORT,
    SET_IMPORT,
    SHOW_IMPORT,
} from "@/actions/project/import";
import { FETCH_PROSPECTS } from "@/actions/project/prospect";
import { UPDATE_PROJECT_USER_SETTING } from "@/actions/project/user/setting";
import { GET_SETTING } from "@/actions/project/setting";
import { OPEN_MODAL } from "@/actions/modal";

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
            notifyWelcomeSms: false,
            welcomeSmsMessageRaw: "",
            welcomeSmsSource: "brevo",
            checkingSmsSourceSetting: false,
            // Mapping between an SMS source and:
            // - the modal to open when it isn't configured
            // - the field(s) that must be present for the
            //   source to be considered "configured"
            smsSourcesSettings: {
                brevo: { modal: "setting-brevo", requiredFields: ["api_key"] },
                smsbox: { modal: "setting-smsbox", requiredFields: ["api_key"] },
                ultramsg: {
                    modal: "setting-ultramsg",
                    requiredFields: ["instance", "token"],
                },
                mtarget: {
                    modal: "setting-mtarget",
                    requiredFields: ["username", "password"],
                },
            },
        };
    },

    methods: {
        // NOTIFY WELCOME SMS

        /**
         * Called when the user (de)activates the
         * "Notifier SMS de bienvenue" checkbox.
         * When activating it, make sure the selected
         * SMS source is configured. If not, open its
         * setting modal and revert the checkbox.
         */
        async onToggleNotifyWelcomeSms() {
            if (this.notifyWelcomeSms) {
                const configured = await this.checkSmsSourceSetting(
                    this.welcomeSmsSource
                );

                if (!configured) {
                    this.notifyWelcomeSms = false;
                    return;
                }
            }

            await this.saveWelcomeSmsSettings();
        },

        /**
         * Called when the user changes the SMS source
         * used to send the welcome message. Re-checks that
         * the newly selected source is properly configured.
         */
        async onChangeWelcomeSmsSource() {
            if (!this.notifyWelcomeSms) {
                return;
            }

            const configured = await this.checkSmsSourceSetting(
                this.welcomeSmsSource
            );

            if (!configured) {
                // Revert to the default source rather than
                // leaving an unconfigured one selected
                this.welcomeSmsSource = "brevo";
                return;
            }

            await this.saveWelcomeSmsSettings();
        },

        /**
         * Verify that the given SMS source has its
         * settings configured for this project. If not,
         * open the corresponding setting modal.
         *
         * @return {boolean} whether the source is configured
         */
        async checkSmsSourceSetting(source) {
            const sourceConfig = this.smsSourcesSettings[source];

            if (!sourceConfig) {
                return false;
            }

            this.checkingSmsSourceSetting = true;

            try {
                const setting = await store.dispatch(GET_SETTING, source);

                const isConfigured =
                    setting &&
                    sourceConfig.requiredFields.every(
                        (field) => !!setting[field]
                    );

                if (!isConfigured) {
                    store.commit(OPEN_MODAL, sourceConfig.modal);
                    return false;
                }

                return true;
            } finally {
                this.checkingSmsSourceSetting = false;
            }
        },

        /**
         * Persist the welcome SMS notification setting
         * (activated + message + source) on the current import
         */
        async saveWelcomeSmsSettings() {
            if (!this.prospectImport || !this.prospectImport.id) {
                return;
            }

            await store.dispatch(UPDATE_IMPORT, {
                id: this.prospectImport.id,
                notify_welcome_sms: this.notifyWelcomeSms,
                welcome_sms_message: this.welcomeSmsMessage,
                welcome_sms_source: this.welcomeSmsSource,
            });
        },

        // LAUNCH AND STOP IMPORT

        /**
         * Launch import
         */
        async processImport() {
            this.processingImport = true;

            // Référence à l'import courant : reste valide même après avoir
            // réinitialisé le panneau (SET_IMPORT null vide juste la sélection,
            // pas l'objet lui-même).
            const currentImport = this.prospectImport;

            try {
                // Launch import
                // (also persists the welcome SMS notification setting,
                // in case it was toggled without triggering the
                // textarea's @change event)
                await store.dispatch(UPDATE_IMPORT, {
                    id: currentImport.id,
                    is_processing: true,
                    notify_welcome_sms: this.notifyWelcomeSms,
                    welcome_sms_message: this.welcomeSmsMessage,
                    welcome_sms_source: this.welcomeSmsSource,
                });
                currentImport.is_processing = true;
                currentImport.notify_welcome_sms = this.notifyWelcomeSms;
                currentImport.welcome_sms_message = this.welcomeSmsMessage;
                currentImport.welcome_sms_source = this.welcomeSmsSource;

                // If prospects table setting is not configured for the current user
                // use the import columns as the default columns for the prospects table
                this.getColumns(currentImport);
                // Update prospects table
                this.refreshProspectsList(currentImport);

                // Flash: "Import is processing"
                flashInfo({
                    title: "Import",
                    body: this.$t("import.process.tab.import.processing"),
                    duration: 5000,
                });

                // Réinitialise le panneau : revient à la liste des imports
                store.commit(SET_IMPORT, null);
                // Rafraîchit la liste des imports (statut à jour)
                store.dispatch(FETCH_IMPORTS);
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
        async setProspectsTableSetting(currentImport) {
            // Appelé aussi depuis un clic (template) où l'argument est un
            // événement : dans ce cas on retombe sur l'import courant du store.
            if (!currentImport || !currentImport.id) {
                currentImport = this.prospectImport;
            }

            this.settingProspectsTable = true;

            try {
                // Get import mapping (données fraîches du serveur)
                const freshImport = await store.dispatch(
                    SHOW_IMPORT,
                    currentImport.id
                );
                const mapping =
                    (freshImport && freshImport.mapping) ||
                    currentImport.mapping ||
                    [];
                const settings = mapping
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
        refreshProspectsList(currentImport = this.prospectImport) {
            const intervalTime = 5000;
            // On capture l'id : l'intervalle continue même après avoir
            // réinitialisé le panneau (import désélectionné).
            const importId = currentImport.id;
            const projectSlug = this.project.slug;
            let interval;

            interval = setInterval(async () => {
                try {
                    // Check if import has been finished
                    const { data } = await ImportService.show(
                        projectSlug,
                        importId
                    );

                    store.dispatch(FETCH_PROSPECTS);

                    // If import is finished
                    // stop refreshing the prospects table
                    if (!data.is_processing) {
                        clearInterval(interval);

                        // Welcome SMS notification:
                        // once the import (dedup + welcome SMS sending)
                        // is done server-side, let the user know.
                        // NOTE: the actual sending of the welcome SMS
                        // (after duplicate check) and the creation of the
                        // "Message bienvenue envoyé" tracking entry on each
                        // newly imported prospect must happen server-side,
                        // as part of the import job, using
                        // `notify_welcome_sms` / `welcome_sms_message`.
                        if (currentImport.notify_welcome_sms) {
                            flashInfo({
                                title: "Import",
                                body: this.$t(
                                    "import.process.tab.import.welcome_sms_sent"
                                ),
                                duration: 5000,
                            });
                        }
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
        async getColumns(currentImport = this.prospectImport) {
            try {
                await store.dispatch(GET_PROJECT_USER_SETTING, {
                    key: "prospects-table",
                });

                // if it is empty
                // use the import columns
                // as the default prospects table columns
                // for the current user
                if (this.projectUserSettingsProspectsTable.length == 0) {
                    this.setProspectsTableSetting(currentImport);
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
                // Réinitialise le flag côté parent pour que le prochain
                // "Suivant" / OK relance bien l'import (sinon "process" reste
                // à true et le watch ne se redéclenche plus → il fallait
                // actualiser la page).
                this.$emit("processed");
            }
        },

        /**
         * Sync local welcome SMS state when
         * the selected import changes
         */
        prospectImport: {
            immediate: true,
            handler(newValue) {
                if (newValue) {
                    this.notifyWelcomeSms = !!newValue.notify_welcome_sms;
                    this.welcomeSmsMessageRaw =
                        newValue.welcome_sms_message || "";
                    this.welcomeSmsSource =
                        newValue.welcome_sms_source || "brevo";
                }
            },
        },
    },

    mounted() {
        // If the import was already processing
        // (eg. the page was refreshed mid-import)
        // resume polling the prospects table
        if (this.prospectImport && this.prospectImport.is_processing) {
            this.refreshProspectsList();
        }
        // Note: the welcome SMS state (notifyWelcomeSms / welcomeSmsMessage)
        // is restored by the immediate "prospectImport" watcher below.
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
         * Welcome SMS message, falling back to the
         * translated default text when empty.
         * (getter/setter so the textarea's v-model
         * keeps working as before)
         */
        welcomeSmsMessage: {
            get() {
                return (
                    this.welcomeSmsMessageRaw ||
                    this.$t(
                        "import.process.tab.import.welcome_sms_message_default"
                    )
                );
            },
            set(value) {
                this.welcomeSmsMessageRaw = value;
            },
        },

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