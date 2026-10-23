<template>
    <modal :name="name" title="CloudTalk" :width="520" @open="resetExport" @closed="resetExport">
        <tab :count="2" :tab="tab" @tab:change="(value) => (tab = value)">
            <template #1>Configuration</template>
            <template #2>Exportation CloudTalk</template>
        </tab>
        <div class="hc-flex-column" style="flex: 1; min-height: 0; overflow: hidden">
            <item-list
                v-show="tab === 0"
                gap="5px"
                class="hc-flex-1"
                padding="10px 0"
            >
                <div class="hc-cloudtalk-webhook">

                    <!-- En-tête -->
                    <div class="hc-cloudtalk-webhook-header">
                        <icon class="fa fa-link" />
                        <span>Recevoir les SMS, appels et messages vocaux</span>
                    </div>

                    <!-- Description -->
                    <p class="hc-cloudtalk-webhook-help">
                        Configurez un webhook CloudTalk pour
                        recevoir automatiquement les nouveaux
                        SMS, les messages vocaux et les enregistrements des appels dans votre CRM.
                    </p>

                    <!-- URL du webhook -->
                    <div class="hc-cloudtalk-webhook-url">
                        <input
                            type="text"
                            readonly
                            :value="cloudtalkWebhookUrl"
                            @focus="$event.target.select()"
                        />

                        <button
                            type="button"
                            class="hc-button-secondary"
                            @click.prevent="copyCloudtalkWebhookUrl"
                        >
                            <icon class="fa fa-copy" />
                            <span>Copier</span>
                        </button>
                    </div>

                    <!-- Instructions détaillées -->
                    <div
                        class="hc-cloudtalk-webhook-instructions"
                    >
                        <div class="hc-cloudtalk-webhook-instructions-title">
                            Comment configurer les événements CloudTalk ?
                        </div>

                        <ol>
                            <li>
                                Accédez aux
                                <a
                                    href="https://dashboard.cloudtalk.io/menu/account/webhooks"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    https://dashboard.cloudtalk.io/menu/account/webhooks
                                </a>.
                            </li>

                            <li>
                                Ajoutez un nouveau webhook
                                dans votre compte CloudTalk.
                            </li>

                            <li>
                                Copiez l'URL ci-dessus et
                                collez-la dans le champ URL
                                du webhook.
                            </li>

                            <li>
                                Sélectionnez les événements
                                <strong>message.received</strong>,
                                <strong>call.ended</strong> et
                                <strong>call.recording_ready</strong>.
                            </li>

                            <li>
                                Enregistrez votre configuration.
                            </li>
                        </ol>

                        <p class="hc-cloudtalk-webhook-note">
                            <icon class="fa fa-info-circle" />
                            Les SMS reçus seront transmis au CRM et les
                            enregistrements seront rattachés aux interactions existantes.
                            Les appels et messages vocaux créeront une interaction si le
                            numéro correspond à un prospect du projet.
                        </p>
                    </div>

                </div>
            </item-list>
            <div v-if="tab === 1 && canCloudtalkExport" class="hc-cloudtalk-export">
                <p>
                    Téléchargez un script PowerShell pour exporter les appels du compte
                    CloudTalk directement sur votre ordinateur.
                </p>
                <p v-if="loadingConfigurations" role="status">Chargement des configurations…</p>
                <template v-else>
                    <form autocomplete="off" @submit.prevent="downloadExportScript">
                        <label v-if="exportConfigurations.length" class="hc-cloudtalk-export-field">
                            <span>Configuration CloudTalk</span>
                            <select v-model="exportLineId" :disabled="downloadingScript" @change="loadExportCredentials">
                                <option :value="null">Saisie manuelle</option>
                                <option v-for="configuration in exportConfigurations" :key="configuration.id" :value="configuration.id">
                                    {{ configurationLabel(configuration) }}
                                </option>
                            </select>
                        </label>
                        <p v-if="loadingCredentials" role="status">Chargement des identifiants…</p>
                        <label class="hc-cloudtalk-export-field">
                            <span>Clé API</span>
                            <input v-model="exportApiKeyId" type="text" autocomplete="off" spellcheck="false" required :disabled="downloadingScript || loadingCredentials" @input="credentialsEdited" />
                        </label>
                        <label class="hc-cloudtalk-export-field">
                            <span>Secret API</span>
                            <input v-model="exportApiKeySecret" type="text" autocomplete="new-password" required :disabled="downloadingScript || loadingCredentials" @input="credentialsEdited" />
                        </label>
                        <p class="hc-cloudtalk-export-help">
                            Les identifiants existants sont préremplis et modifiables.
                            Les modifications servent uniquement au script et ne sont pas enregistrées dans la ligne.
                        </p>
                        <p class="hc-cloudtalk-export-help">
                            L’export couvre tous les appels accessibles sur ce compte CloudTalk,
                            y compris ceux sans prospect dans le CRM.
                        </p>
                        <div class="hc-cloudtalk-export-dates">
                            <label class="hc-cloudtalk-export-field">
                                <span>Date de début</span>
                                <input v-model="exportDateFrom" type="date" required :max="exportDateTo || undefined" :disabled="downloadingScript" />
                            </label>
                            <label class="hc-cloudtalk-export-field">
                                <span>Date de fin</span>
                                <input v-model="exportDateTo" type="date" required :min="exportDateFrom || undefined" :disabled="downloadingScript" />
                            </label>
                        </div>
                        <p class="hc-cloudtalk-export-help">
                            Journées incluses, fuseau horaire : {{ exportTimezone }}.
                        </p>
                        <label class="hc-cloudtalk-export-checkbox">
                            <input v-model="exportDownloadAudio" type="checkbox" :disabled="downloadingScript" />
                            <span>Télécharger les audios disponibles</span>
                        </label>
                        <p class="hc-cloudtalk-export-help">
                            Résultat : appels.csv et, si demandé, un dossier audios/.
                            La colonne audio_path indique le chemin relatif de chaque audio téléchargé.
                        </p>
                        <button class="hc-button-secondary" type="submit" :disabled="downloadingScript || !canDownloadExport">
                            <icon :class="downloadingScript ? 'fa fa-spinner fa-spin' : 'fa fa-download'" />
                            {{ downloadingScript ? 'Préparation du script…' : 'Télécharger le script PowerShell' }}
                        </button>
                    </form>
                </template>
                <p v-if="exportError" class="hc-cloudtalk-export-error" role="alert">{{ exportError }}</p>
                <button v-if="exportError && !exportConfigurations.length" class="hc-button-secondary" type="button" :disabled="loadingConfigurations" @click="loadExportConfigurations">
                    Réessayer
                </button>
                <p v-if="downloadedScriptName" class="hc-cloudtalk-export-help" role="status">
                    Script téléchargé. Ouvrez PowerShell dans votre dossier de téléchargement et lancez :
                    <code>.\{{ downloadedScriptName }}</code>
                    Si Windows bloque l’exécution, utilisez cette commande, valable uniquement pour ce lancement :
                    <code>powershell.exe -NoProfile -ExecutionPolicy Bypass -File ".\{{ downloadedScriptName }}"</code>
                </p>
                <p class="hc-cloudtalk-webhook-note">
                    Le script contient la clé API et le secret CloudTalk. Conservez-le dans un endroit privé.
                    Il s’exécute sous Windows avec PowerShell et télécharge les données directement depuis CloudTalk.
                </p>
            </div>
            <p v-else-if="tab === 1" class="hc-cloudtalk-export-help">
                L’exportation CloudTalk est réservée aux administrateurs du projet.
            </p>
        </div>
    </modal>
</template>

<style>
.hc-cloudtalk-export {
    padding: 10px;
    overflow-y: auto;
    font-size: 12px;
    line-height: 1.5;
}

.hc-cloudtalk-export > p:first-child {
    margin-top: 0;
}

.hc-cloudtalk-export-field {
    display: flex;
    flex: 1;
    flex-direction: column;
    gap: 5px;
    min-width: 0;
    margin-bottom: 10px;
}

.hc-cloudtalk-export-field input,
.hc-cloudtalk-export-field select {
    width: 100%;
    padding: 7px;
    border: 1px solid #dddddd;
    border-radius: 4px;
    background: #ffffff;
    color: #333333;
}

.hc-cloudtalk-export-dates {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.hc-cloudtalk-export-checkbox {
    display: flex;
    align-items: center;
    gap: 7px;
}

.hc-cloudtalk-export-help {
    color: #666666;
    margin: 8px 0 12px;
}

.hc-cloudtalk-export-help code {
    display: block;
    overflow-wrap: anywhere;
    margin-top: 5px;
}

.hc-cloudtalk-export-error {
    color: #c0392b;
}

.hc-cloudtalk-export button:disabled {
    opacity: 0.6;
    cursor: default;
}

.dark .hc-cloudtalk-export-field input,
.dark .hc-cloudtalk-export-field select {
    border-color: #444444;
    background: #2a2a2a;
    color: #dddddd;
}

.dark .hc-cloudtalk-export-help {
    color: #bbbbbb;
}

.hc-cloudtalk-webhook {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #d6e4f0;
    border-radius: 6px;
    background: #f4f9ff;
}

.hc-cloudtalk-webhook-header {
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
    color: #12a0f3;
}

.hc-cloudtalk-webhook-help {
    margin: 0;
    font-size: 12px;
    line-height: 1.4;
    color: #495057;
}

.hc-cloudtalk-webhook-url {
    display: flex;
    align-items: stretch;
    gap: 6px;
}

.hc-cloudtalk-webhook-url input {
    flex: 1;
    min-width: 0;
    padding: 5px 10px;
    border: 1px solid #dddddd;
    border-radius: 6px;
    background: #ffffff;
    color: #333333;
    font-size: 12px;
}

/* Instructions */
.hc-cloudtalk-webhook-instructions {
    padding: 10px 0 0;
    border-top: 1px solid #d6e4f0;
    font-size: 12px;
    line-height: 1.6;
    color: #495057;
}

.hc-cloudtalk-webhook-instructions-title {
    font-weight: 600;
    margin-bottom: 8px;
}

.hc-cloudtalk-webhook-instructions ol {
    margin: 0;
    padding-left: 20px;
}

.hc-cloudtalk-webhook-instructions li {
    margin-bottom: 7px;
}

.hc-cloudtalk-webhook-instructions strong {
    color: #12a0f3;
    font-weight: 600;
}

.hc-cloudtalk-webhook-instructions a {
    color: #12a0f3;
    text-decoration: none;
    font-weight: 600;
}

.hc-cloudtalk-webhook-instructions a:hover {
    text-decoration: underline;
}

.hc-cloudtalk-webhook-instructions a .fa {
    font-size: 10px;
    margin-left: 3px;
}

/* Note d'information */
.hc-cloudtalk-webhook-note {
    display: flex;
    align-items: flex-start;
    gap: 6px;
    margin: 8px 0 0;
    padding: 8px;
    border-radius: 4px;
    background: #e6f3ff;
    color: #495057;
    font-size: 11px;
    line-height: 1.5;
}

.hc-cloudtalk-webhook-note .fa {
    color: #12a0f3;
    margin-top: 2px;
}

/* Mode sombre */
.dark .hc-cloudtalk-webhook {
    border-color: #334155;
    background: #1e293b;
}

.dark .hc-cloudtalk-webhook-help {
    color: #bbbbbb;
}

.dark .hc-cloudtalk-webhook-url input {
    border-color: #444444;
    background: #2a2a2a;
    color: #dddddd;
}

.dark .hc-cloudtalk-webhook-instructions {
    border-color: #334155;
    color: #bbbbbb;
}

.dark .hc-cloudtalk-webhook-note {
    background: #263b53;
    color: #cccccc;
}
</style>

<script>
import { mapGetters } from "vuex";
import ApiService from "@/apis/api.service";

export default {
    data() {
        const today = new Date();
        const dateTo = [today.getFullYear(), String(today.getMonth() + 1).padStart(2, "0"), String(today.getDate()).padStart(2, "0")].join("-");
        let timezone = "UTC";
        try {
            timezone = Intl.DateTimeFormat().resolvedOptions().timeZone || "UTC";
        } catch (error) {
            // UTC reste disponible si le navigateur ne fournit aucun fuseau horaire.
        }
        return {
            name: "setting-cloudtalk",
            tab: 0,
            exportConfigurations: [],
            exportLineId: null,
            exportApiKeyId: "",
            exportApiKeySecret: "",
            exportDateFrom: dateTo.slice(0, 8) + "01",
            exportDateTo: dateTo,
            exportTimezone: timezone,
            exportDownloadAudio: false,
            loadingConfigurations: false,
            loadingCredentials: false,
            downloadingScript: false,
            exportError: "",
            downloadedScriptName: "",
            exportRequestVersion: 0,
            exportCredentialsVersion: 0,
        };
    },

    methods: {
        configurationLabel(configuration) {
            return [configuration.name || "Ligne " + configuration.id, configuration.numero, configuration.agent_name].filter(Boolean).join(" — ");
        },

        async loadExportConfigurations() {
            if (!this.canCloudtalkExport || !this.projectSlug) {
                return;
            }
            const version = ++this.exportRequestVersion;
            const slug = this.projectSlug;
            this.loadingConfigurations = true;
            this.exportError = "";
            this.exportConfigurations = [];
            this.exportLineId = null;
            this.clearExportCredentials();
            try {
                const { data } = await ApiService.get(`project/${slug}/line/cloudtalk/export-configurations`);
                if (version !== this.exportRequestVersion || !this.canCloudtalkExport) return;
                this.exportConfigurations = data.configurations || [];
                this.exportLineId = this.exportConfigurations.length ? this.exportConfigurations[0].id : null;
                await this.loadExportCredentials();
            } catch (error) {
                const message = await this.exportErrorMessage(error, "Impossible de charger les configurations CloudTalk.");
                if (version === this.exportRequestVersion) this.exportError = message;
            } finally {
                if (version === this.exportRequestVersion) this.loadingConfigurations = false;
            }
        },

        clearExportCredentials() {
            this.exportCredentialsVersion++;
            this.exportApiKeyId = "";
            this.exportApiKeySecret = "";
            this.loadingCredentials = false;
        },

        credentialsEdited() {
            this.exportCredentialsVersion++;
            this.loadingCredentials = false;
            this.downloadedScriptName = "";
        },

        async loadExportCredentials() {
            this.clearExportCredentials();
            this.exportError = "";
            this.downloadedScriptName = "";
            if (!this.canCloudtalkExport || !this.projectSlug || this.exportLineId === null) return;
            const version = this.exportCredentialsVersion;
            const slug = this.projectSlug;
            const lineId = this.exportLineId;
            this.loadingCredentials = true;
            try {
                const { data } = await ApiService.get(`project/${slug}/line/cloudtalk/export-credentials/${lineId}`);
                if (version !== this.exportCredentialsVersion || slug !== this.projectSlug ||
                    lineId !== this.exportLineId || !this.canCloudtalkExport) return;
                this.exportApiKeyId = data.api_key_id || "";
                this.exportApiKeySecret = data.api_key_secret || "";
            } catch (error) {
                const message = await this.exportErrorMessage(error, "Impossible de charger les identifiants. Vous pouvez les saisir manuellement.");
                if (version === this.exportCredentialsVersion) this.exportError = message;
            } finally {
                if (version === this.exportCredentialsVersion) this.loadingCredentials = false;
            }
        },

        async exportErrorMessage(error, fallback) {
            let data = error.response && error.response.data;
            if (data instanceof Blob) {
                try {
                    data = JSON.parse(await data.text());
                } catch (parseError) {
                    return fallback;
                }
            }
            if (data && data.errors) {
                return Object.values(data.errors).flat().join(" ");
            }
            return data && typeof data.message === "string" ? data.message : fallback;
        },

        async downloadExportScript() {
            if (!this.canDownloadExport || this.downloadingScript) return;
            const version = ++this.exportRequestVersion;
            const slug = this.projectSlug;
            const filename = `cloudtalk-export-${this.exportDateFrom}-${this.exportDateTo}.ps1`;
            this.downloadingScript = true;
            this.exportError = "";
            this.downloadedScriptName = "";
            try {
                const { data } = await ApiService.post(`project/${slug}/line/cloudtalk/export-script`, {
                    line_id: this.exportLineId,
                    api_key_id: this.exportApiKeyId,
                    api_key_secret: this.exportApiKeySecret,
                    date_from: this.exportDateFrom,
                    date_to: this.exportDateTo,
                    download_audio: this.exportDownloadAudio,
                    timezone: this.exportTimezone,
                }, { responseType: "blob" });
                if (version !== this.exportRequestVersion || !this.canCloudtalkExport) return;
                const url = URL.createObjectURL(data);
                const link = document.createElement("a");
                link.href = url;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                link.remove();
                setTimeout(() => URL.revokeObjectURL(url), 1000);
                this.downloadedScriptName = filename;
            } catch (error) {
                const message = await this.exportErrorMessage(error, "Impossible de télécharger le script CloudTalk.");
                if (version === this.exportRequestVersion) this.exportError = message;
            } finally {
                if (version === this.exportRequestVersion) this.downloadingScript = false;
            }
        },

        resetExport() {
            this.exportRequestVersion++;
            this.tab = 0;
            this.exportConfigurations = [];
            this.exportLineId = null;
            this.clearExportCredentials();
            this.loadingConfigurations = false;
            this.downloadingScript = false;
            this.exportError = "";
            this.downloadedScriptName = "";
        },

        copyCloudtalkWebhookUrl() {
            if (!this.cloudtalkWebhookUrl) {
                return;
            }

            navigator.clipboard
                .writeText(this.cloudtalkWebhookUrl)
                .then(() => {
                    flashInfo({
                        title: "CloudTalk",
                        body: "URL du webhook CloudTalk copiée",
                        duration: 5000,
                    });
                })
                .catch(() => {
                    flashInfo({
                        title: "CloudTalk",
                        body: "Impossible de copier l'URL du webhook",
                        duration: 5000,
                    });
                });
        },
    },

    beforeUnmount() {
        this.exportRequestVersion++;
        this.clearExportCredentials();
    },

    watch: {
        tab(value) {
            if (value === 1 && this.canCloudtalkExport && !this.exportConfigurations.length &&
                !this.loadingConfigurations && !this.downloadingScript) this.loadExportConfigurations();
        },
        projectSlug() {
            this.resetExport();
        },
        user(value, previous) {
            if ((value && value.id) !== (previous && previous.id)) this.resetExport();
        },
        canCloudtalkExport(value) {
            if (!value) this.resetExport();
            else if (this.tab === 1) this.loadExportConfigurations();
        },
    },

    computed: {
        ...mapGetters(["project", "can"]),
        ...mapGetters("auth", ["user"]),

        projectSlug() {
            return this.project && this.project.slug;
        },

        canCloudtalkExport() {
            return Boolean(this.can('all'));
        },

        canDownloadExport() {
            return this.canCloudtalkExport && this.projectSlug && !this.loadingConfigurations && !this.loadingCredentials &&
                (this.exportLineId === null || this.exportConfigurations.some((configuration) => configuration.id === this.exportLineId)) &&
                this.exportApiKeyId.trim().length > 0 && this.exportApiKeySecret.trim().length > 0 &&
                /^\d{4}-\d{2}-\d{2}$/.test(this.exportDateFrom) &&
                /^\d{4}-\d{2}-\d{2}$/.test(this.exportDateTo) &&
                this.exportDateFrom <= this.exportDateTo;
        },

        /**
         * URL du webhook CloudTalk pour
         * recevoir les SMS entrants et les références des enregistrements.
         */
        cloudtalkWebhookUrl() {
            if (!this.project || !this.project.slug) {
                return "";
            }

            return (
                window.location.origin +
                "/webhook/project/" +
                this.project.slug +
                "/cloudtalk"
            );
        },
    },
};
</script>
