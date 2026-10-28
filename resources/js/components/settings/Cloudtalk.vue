<template>
    <modal :name="name" title="CloudTalk" :width="520" @open="resetExport" @closed="resetExport">
        <tab :count="3" :tab="tab" @tab:change="(value) => (tab = value)">
            <template #1>Configuration</template>
            <template #2>Exportation appel CloudTalk</template>
            <template #3>Exportation SMS</template>
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
                        <span>Synchroniser les SMS reçus et envoyés, les appels et messages vocaux</span>
                    </div>

                    <!-- Description -->
                    <p class="hc-cloudtalk-webhook-help">
                        Configurez un webhook CloudTalk pour enregistrer les SMS reçus
                        et ceux envoyés depuis CloudTalk Phone ou une autre application CloudTalk,
                        ainsi que les messages vocaux et les enregistrements des appels.
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
                                Connectez-vous à CloudTalk avec un compte administrateur,
                                puis ouvrez <strong>Paramètres → Webhooks</strong> :
                                <a
                                    href="https://dashboard.cloudtalk.io/menu/account/webhooks"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    https://dashboard.cloudtalk.io/menu/account/webhooks
                                </a>.
                            </li>

                            <li>
                                Ajoutez un nouveau webhook ou modifiez celui déjà configuré pour ce CRM.
                            </li>

                            <li>
                                Copiez l'URL ci-dessus et
                                collez-la dans le champ URL
                                du webhook. Utilisez une URL publique en HTTPS, accessible depuis CloudTalk.
                            </li>

                            <li>
                                Sélectionnez les événements
                                <strong>message.received</strong>,
                                <strong>message.sent</strong>,
                                <strong>call.ended</strong> et
                                <strong>call.recording_ready</strong>.
                            </li>

                            <li>
                                Enregistrez votre configuration, puis envoyez un SMS depuis CloudTalk
                                vers le numéro d’un prospect du projet pour vérifier son apparition dans le CRM.
                            </li>
                        </ol>

                        <p class="hc-cloudtalk-webhook-note">
                            <icon class="fa fa-info-circle" />
                            Les SMS sont rattachés au prospect dont le numéro correspond dans ce projet.
                            Un SMS déjà enregistré lors d’un envoi depuis le CRM, ou un événement reçu
                            plusieurs fois, est reconnu pour éviter les doublons. Les enregistrements
                            restent rattachés aux appels correspondants.
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
                        <p v-if="loadingCredentials" role="status">Recherche d’identifiants CloudTalk valides…</p>
                        <template v-if="showExportCredentials">
                            <label class="hc-cloudtalk-export-field">
                                <span>Clé API</span>
                                <input v-model="exportApiKeyId" type="text" autocomplete="off" spellcheck="false" required :disabled="downloadingScript || loadingCredentials" @input="credentialsEdited" />
                            </label>
                            <label class="hc-cloudtalk-export-field">
                                <span>Secret API</span>
                                <input v-model="exportApiKeySecret" type="text" autocomplete="new-password" required :disabled="downloadingScript || loadingCredentials" @input="credentialsEdited" />
                            </label>
                            <p class="hc-cloudtalk-export-help">
                                Les identifiants CloudTalk valides sont préremplis automatiquement et restent modifiables.
                                Les modifications servent uniquement au script et ne sont pas enregistrées dans la ligne.
                            </p>
                        </template>
                        <button v-else type="button" class="hc-cloudtalk-edit-key" :disabled="downloadingScript || loadingCredentials" @click="showExportCredentials = true">
                            Modifier la clé
                        </button>
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
                        <!-- <p class="hc-cloudtalk-export-help">
                            Journées incluses, fuseau horaire : {{ exportTimezone }}.
                        </p> -->
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
                <button v-if="exportError && !exportApiKeyId && !exportApiKeySecret" class="hc-button-secondary" type="button" :disabled="loadingConfigurations || loadingCredentials" @click="loadExportConfigurations">
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
            <div v-if="tab === 2 && canCloudtalkExport" class="hc-cloudtalk-export">
                <p>Exportez les SMS entrants et sortants enregistrés dans le CRM pour le projet actuel.</p>
                <form @submit.prevent="downloadSmsExport">
                    <div class="hc-cloudtalk-export-dates">
                        <label class="hc-cloudtalk-export-field">
                            <span>Date de début</span>
                            <input v-model="smsDateFrom" type="date" required :max="smsDateTo || undefined" :disabled="downloadingSms" />
                        </label>
                        <label class="hc-cloudtalk-export-field">
                            <span>Date de fin</span>
                            <input v-model="smsDateTo" type="date" required :min="smsDateFrom || undefined" :disabled="downloadingSms" />
                        </label>
                    </div>
                    <p class="hc-cloudtalk-export-help">
                        Le CSV contient les messages, dates, sens, sources, prospects et auteurs.
                        Toutes les sources de SMS enregistrées dans le CRM sont incluses.
                    </p>
                    <button class="hc-button-secondary" type="submit" :disabled="downloadingSms || !canDownloadSms">
                        <icon :class="downloadingSms ? 'fa fa-spinner fa-spin' : 'fa fa-download'" />
                        {{ downloadingSms ? 'Préparation du CSV…' : 'Télécharger les SMS en CSV' }}
                    </button>
                </form>
                <p v-if="smsProgress" class="hc-cloudtalk-export-help" role="status">{{ smsProgress }}</p>
                <p v-if="smsError" class="hc-cloudtalk-export-error" role="alert">{{ smsError }}</p>
                <p v-if="downloadedSmsName" class="hc-cloudtalk-export-help" role="status">Fichier téléchargé : {{ downloadedSmsName }}</p>
                <p class="hc-cloudtalk-export-help">
                    Le fichier est préparé en arrière-plan depuis la base du CRM, puis téléchargé automatiquement.
                    Choisissez une période plus courte pour limiter sa taille.
                </p>
            </div>
            <p v-else-if="tab === 2" class="hc-cloudtalk-export-help">
                L’exportation SMS est réservée aux administrateurs du projet.
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

.hc-cloudtalk-export-field input {
    width: 100%;
    padding: 7px;
    border: 1px solid #dddddd;
    border-radius: 4px;
    background: #ffffff;
    color: #333333;
}

.hc-cloudtalk-edit-key {
    border: 0;
    background: none;
    padding: 0;
    margin-bottom: 10px;
    color: #12a0f3;
    font: inherit;
    cursor: pointer;
}

.hc-cloudtalk-edit-key:hover {
    text-decoration: underline;
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

.dark .hc-cloudtalk-export-field input {
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
import axios from "axios";

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
            showExportCredentials: true,
            exportDateFrom: dateTo,
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
            smsDateFrom: dateTo,
            smsDateTo: dateTo,
            downloadingSms: false,
            smsProgress: "",
            smsError: "",
            downloadedSmsName: "",
            smsRequestVersion: 0,
            smsPollTimer: null,
            smsPollResolve: null,
        };
    },

    methods: {
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
                if (version !== this.exportRequestVersion || slug !== this.projectSlug || !this.canCloudtalkExport) return;
                this.exportConfigurations = data.configurations || [];
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
            this.showExportCredentials = true;
            this.loadingCredentials = false;
        },

        credentialsEdited() {
            this.showExportCredentials = true;
            this.exportCredentialsVersion++;
            this.loadingCredentials = false;
            this.downloadedScriptName = "";
        },

        async loadExportCredentials() {
            this.clearExportCredentials();
            this.exportLineId = null;
            this.exportError = "";
            this.downloadedScriptName = "";
            if (!this.canCloudtalkExport || !this.projectSlug) return;
            const version = this.exportCredentialsVersion;
            const slug = this.projectSlug;
            const isCurrent = () => version === this.exportCredentialsVersion && slug === this.projectSlug && this.canCloudtalkExport;
            const checked = new Set();
            this.loadingCredentials = true;
            try {
                for (const configuration of this.exportConfigurations) {
                    if (!isCurrent()) return;
                    let credentials;
                    try {
                        const { data } = await ApiService.get(`project/${slug}/line/cloudtalk/export-credentials/${configuration.id}`);
                        credentials = {
                            api_key_id: typeof data.api_key_id === "string" ? data.api_key_id.trim() : "",
                            api_key_secret: typeof data.api_key_secret === "string" ? data.api_key_secret.trim() : "",
                        };
                    } catch (error) {
                        if (!isCurrent()) return;
                        if (error.response?.status === 404) continue;
                        throw error;
                    }
                    if (!isCurrent()) return;
                    if (!credentials.api_key_id || !credentials.api_key_secret) continue;
                    const pair = JSON.stringify(credentials);
                    if (checked.has(pair)) continue;
                    checked.add(pair);
                    try {
                        const { data } = await ApiService.post(`project/${slug}/line/cloudtalk/verify`, credentials);
                        if (!isCurrent()) return;
                        if (data.valid !== true) continue;
                    } catch (error) {
                        if (!isCurrent()) return;
                        const providerStatus = Number(error.response?.data?.status);
                        if ([401, 403, 422].includes(providerStatus) || error.response?.status === 422) continue;
                        throw error;
                    }
                    this.exportLineId = configuration.id;
                    this.exportApiKeyId = credentials.api_key_id;
                    this.exportApiKeySecret = credentials.api_key_secret;
                    this.showExportCredentials = false;
                    return;
                }
                if (isCurrent()) {
                    this.exportError = "Aucune clé API CloudTalk valide n’a été trouvée. Vérifiez les clés configurées dans vos lignes ou saisissez-les manuellement.";
                }
            } catch (error) {
                const message = await this.exportErrorMessage(error, "Impossible de vérifier les identifiants CloudTalk. Réessayez ou saisissez-les manuellement.");
                if (isCurrent()) this.exportError = message;
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

        async downloadSmsExport() {
            if (!this.canDownloadSms || this.downloadingSms) return;
            const version = ++this.smsRequestVersion;
            const slug = this.projectSlug;
            const filename = `sms-${this.smsDateFrom}-${this.smsDateTo}.csv`;
            this.downloadingSms = true;
            this.smsProgress = "Préparation de l’export SMS…";
            this.smsError = "";
            this.downloadedSmsName = "";
            try {
                const { data } = await ApiService.post(`project/${slug}/sms/export`, {
                    date_from: this.smsDateFrom,
                    date_to: this.smsDateTo,
                    timezone: this.exportTimezone,
                    count: 0,
                });
                if (!this.smsRequestIsCurrent(version, slug)) return;
                if (!data || typeof data.status_url !== "string" || !data.status_url ||
                    typeof data.download_url !== "string" || !data.download_url) {
                    this.smsError = "La réponse de préparation de l’export SMS est invalide.";
                    return;
                }
                this.smsProgress = data.message || "Export SMS en attente…";
                const ready = await this.waitForSmsDownload(data.status_url, version, slug);
                if (!ready || !this.smsRequestIsCurrent(version, slug)) return;
                const link = document.createElement("a");
                link.href = data.download_url;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                link.remove();
                this.downloadedSmsName = filename;
            } catch (error) {
                const message = await this.exportErrorMessage(error, "Impossible de télécharger les SMS du CRM.");
                if (this.smsRequestIsCurrent(version, slug)) this.smsError = message;
            } finally {
                if (version === this.smsRequestVersion) {
                    this.downloadingSms = false;
                    this.smsProgress = "";
                    this.cancelSmsPolling();
                }
            }
        },

        smsRequestIsCurrent(version, slug) {
            return version === this.smsRequestVersion && slug === this.projectSlug && this.canCloudtalkExport;
        },

        cancelSmsPolling() {
            if (this.smsPollTimer !== null) clearTimeout(this.smsPollTimer);
            this.smsPollTimer = null;
            const resolve = this.smsPollResolve;
            this.smsPollResolve = null;
            if (resolve) resolve(false);
        },

        waitForSmsPoll(version, slug) {
            return new Promise((resolve) => {
                if (!this.smsRequestIsCurrent(version, slug)) return resolve(false);
                this.smsPollResolve = resolve;
                this.smsPollTimer = setTimeout(() => {
                    this.smsPollTimer = null;
                    this.smsPollResolve = null;
                    resolve(this.smsRequestIsCurrent(version, slug));
                }, 3000);
            });
        },

        async waitForSmsDownload(statusUrl, version, slug) {
            while (this.smsRequestIsCurrent(version, slug)) {
                const { data } = await axios.get(statusUrl);
                if (!this.smsRequestIsCurrent(version, slug)) return false;
                if (data && data.status === "ready") return true;
                if (!data || !["queued", "processing"].includes(data.status)) {
                    this.smsError = data && data.message || "La préparation de l’export SMS a échoué. Relancez l’exportation.";
                    return false;
                }
                this.smsProgress = data.message || (data.status === "queued"
                    ? "Export SMS en attente…"
                    : "Préparation du CSV en cours…");
                if (!await this.waitForSmsPoll(version, slug)) return false;
            }
            return false;
        },

        resetExport() {
            this.exportRequestVersion++;
            this.smsRequestVersion++;
            this.cancelSmsPolling();
            this.downloadingSms = false;
            this.smsProgress = "";
            this.smsError = "";
            this.downloadedSmsName = "";
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
        this.smsRequestVersion++;
        this.cancelSmsPolling();
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

        canDownloadSms() {
            const validDate = (value) => {
                if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) return false;
                const parsed = new Date(`${value}T00:00:00Z`);
                return !Number.isNaN(parsed.getTime()) && parsed.toISOString().slice(0, 10) === value;
            };
            return Boolean(this.canCloudtalkExport && this.projectSlug &&
                validDate(this.smsDateFrom) && validDate(this.smsDateTo) &&
                this.smsDateFrom <= this.smsDateTo);
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
