<template>
    <div class="hc-line-operator-config-fields">
        <div v-if="operator === 'cloudtalk'" class="hc-cloudtalk-api-help">
            <a href="#" @click.prevent="openCloudtalkApiHelp">
                Comment récupérer les clés API ?
            </a>
        </div>
        <v-field
            v-for="field in fields"
            :key="field.key"
            :label="field.label"
            :required="fieldRequired(field)"
            v-slot="{ label }"
        >
            <div
                v-if="operator === 'cloudtalk' && field.key === 'agent_id'"
                class="hc-cloudtalk-agent-loader"
            >
                <button
                    type="button"
                    class="hc-button-secondary"
                    :disabled="fetchingCloudtalkAgents || !cloudtalkCredentialsReady"
                    @click.prevent="fetchCloudtalkAgents(true)"
                >
                    {{
                        fetchingCloudtalkAgents
                            ? "Vérification en cours..."
                            : "Tester et charger les agents"
                    }}
                </button>
                <div
                    v-if="cloudtalkMessage"
                    :class="['hc-cloudtalk-agent-message', cloudtalkMessageType]"
                    v-text="cloudtalkMessage"
                ></div>
            </div>
            <select
                v-if="field.type === 'select'"
                v-model="config[field.key]"
                :disabled="fieldDisabled(field)"
                :required="fieldRequired(field)"
            >
                <option value="" disabled></option>
                <option
                    v-for="option in fieldOptions(field)"
                    :key="option.value"
                    :value="option.value"
                    v-text="option.label"
                ></option>
            </select>
            <div
                v-else-if="
                    isSensitiveField(field) &&
                    hasFieldValue(field) &&
                    !editingFields[field.key]
                "
                class="hc-line-secret-preview"
            >
                <span
                    class="hc-line-secret-preview-value"
                    v-text="maskedValue(field)"
                ></span>
                <button
                    type="button"
                    class="hc-line-secret-preview-edit"
                    :title="$t('edit')"
                    @click.prevent="editField(field)"
                >
                    <icon class="fa fa-pen" />
                </button>
            </div>
            <input
                v-else
                :type="field.type"
                :placeholder="label + ' ...'"
                v-model="config[field.key]"
                :required="fieldRequired(field)"
            />
        </v-field>

        <div
            v-if="cloudtalkAgentSmsNumber"
            class="hc-cloudtalk-sms-confirmation"
        >
            <p>
                Numéro de l’agent : <strong>{{ cloudtalkAgentSmsNumber }}</strong>
            </p>
            <label>
                <input
                    type="checkbox"
                    :checked="cloudtalkSmsConfirmed"
                    @change="confirmCloudtalkSmsNumber($event.target.checked)"
                />
                <span>Je confirme que ce numéro peut envoyer des SMS.</span>
            </label>
            <!-- <p>Vérifiez l’activation des SMS dans CloudTalk avant de confirmer. Le numéro sera alors renseigné dans le champ SMS.</p> -->
        </div>

        <section v-if="projects.length > 0 && userId" class="hc-config-project-picker">
            <div class="hc-config-project-picker-heading">
                <strong>Enregistrer cette ligne dans d’autres projets</strong>
                <span>La configuration sélectionnée sera créée ou mise à jour dans chaque projet coché.</span>
            </div>
            <div class="hc-config-project-list">
                <label v-for="project in projects" :key="project.id" class="hc-config-project-option">
                    <input
                        type="checkbox"
                        :value="project.slug"
                        :checked="sourceProjects.includes(project.slug)"
                        :disabled="loadingProjectConfig"
                        @change="toggleSourceProject(project.slug, $event.target.checked)"
                    />
                    <span class="hc-config-project-option-name" v-text="project.name"></span>
                    <icon v-if="sourceProjects.includes(project.slug)" class="fa fa-check hc-config-project-check" />
                </label>
            </div>
            <div v-if="loadingProjectConfig" class="hc-config-project-status">
                <span class="hc-config-project-spinner"></span> Chargement des configurations…
            </div>
            <div v-else-if="projectConfigMessage" class="hc-config-project-status" v-text="projectConfigMessage"></div>
            <div v-else-if="sourceProjects.length" class="hc-config-project-status">
                {{ sourceProjects.length }} projet(s) sélectionné(s)
            </div>
        </section>

        <div v-if="operator === 'cloudtalk'" class="hc-cloudtalk-api-help">
            <a href="#" @click.prevent="openCloudtalkWebhookModal">
                comment recevoir des SMS ?
            </a>
        </div>

        <kavkom-diagnostic v-if="operator === 'kavkom'" :config="config" />

        <modal name="cloudtalk-api-help" title="Récupérer les clés API CloudTalk" :width="520">
            <div class="hc-cloudtalk-api-instructions">
                <p>Pour récupérer l’<strong>API Key ID</strong> et l’<strong>API Key Secret</strong> :</p>
                <ol>
                    <li>Connectez-vous à votre compte CloudTalk avec un compte administrateur.</li>
                    <li>Ouvrez <strong>Account → Settings → Integrations and automation → API Keys</strong>.</li>
                    <li>Cliquez sur <strong>Add API Key</strong> pour créer une clé dédiée à cette intégration.</li>
                    <li>Copiez l’<strong>Access Key ID</strong> et l’<strong>Access Key Secret</strong> dans les champs correspondants ici.</li>
                </ol>
                <p>Gardez le Secret confidentiel. Si la page ne permet plus de le consulter, créez une nouvelle clé.</p>
                <a href="https://dashboard.cloudtalk.io/menu/account/settings/API-keys" target="_blank" rel="noopener noreferrer">
                    Ouvrir le tableau de bord CloudTalk <icon class="fa fa-external-link-alt" />
                </a>
            </div>
        </modal>
    </div>
</template>

<style>
.hc-line-operator-config-fields {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.hc-cloudtalk-api-help {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 6px;
    padding: 2px 0;
    font-size: 11px;
}

.hc-cloudtalk-api-help a {
    color: #12a0f3;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
}

.hc-cloudtalk-api-instructions {
    padding: 4px 8px 12px;
    color: #334155;
    font-size: 13px;
    line-height: 1.6;
}

.hc-cloudtalk-api-instructions ol {
    padding-left: 22px;
}

.hc-cloudtalk-api-instructions li {
    margin: 8px 0;
}

.hc-cloudtalk-api-instructions a {
    color: #12a0f3;
    font-weight: 600;
}

.dark .hc-cloudtalk-api-instructions {
    color: #e2e8f0;
}

.hc-cloudtalk-api-help a:hover {
    text-decoration: underline;
}

.hc-config-project-picker {
    padding: 12px;
    border: 1px solid #d9e2ec;
    border-radius: 8px;
    background: #f8fafc;
}

.hc-config-project-picker-heading {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-bottom: 10px;
    color: #334155;
    font-size: 13px;
}

.hc-config-project-picker-heading span,
.hc-config-project-status {
    color: #64748b;
    font-size: 12px;
}

.hc-config-project-list {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    max-height: 180px;
    overflow-y: auto;
}

.hc-config-project-option {
    display: flex;
    align-items: center;
    gap: 9px;
    min-width: 0;
    padding: 9px 10px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: #fff;
    cursor: pointer;
}

.hc-config-project-option:has(input:checked) {
    border-color: #12a0f3;
    background: #eff8ff;
}

.hc-config-project-option input {
    flex: 0 0 auto;
    margin: 0;
    accent-color: #12a0f3;
}

.hc-config-project-option-name {
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #334155;
    font-size: 12px;
}

.hc-config-project-check {
    color: #12a0f3;
    font-size: 12px;
}

.hc-config-project-status {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-top: 9px;
}

.hc-config-project-spinner {
    width: 13px;
    height: 13px;
    border: 2px solid #cbd5e1;
    border-top-color: #12a0f3;
    border-radius: 50%;
    animation: hc-config-spin 0.7s linear infinite;
}

@keyframes hc-config-spin {
    to { transform: rotate(360deg); }
}

.dark .hc-config-project-picker {
    border-color: #334155;
    background: #1e293b;
}

.dark .hc-config-project-picker-heading,
.dark .hc-config-project-option-name {
    color: #e2e8f0;
}

.dark .hc-config-project-option {
    border-color: #334155;
    background: #0f172a;
}

.dark .hc-config-project-option:has(input:checked) {
    background: #172f45;
}

.hc-line-secret-preview {
    display: flex;
    align-items: center;
    gap: 8px;
    min-height: 34px;
}

.hc-line-secret-preview-value {
    flex: 1;
    min-width: 0;
    padding: 5px 10px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #333333;
}

.hc-line-secret-preview-edit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 34px;
    width: 34px;
    min-height: 34px;
    border: none;
    border-left: 1px solid #dddddd;
    background: transparent;
    color: #12a0f3;
    cursor: pointer;
}

.hc-line-secret-preview-edit:focus {
    outline: 2px solid #12a0f3;
    outline-offset: -2px;
}

.dark .hc-line-secret-preview-value {
    color: #bbbbbb;
}

.dark .hc-line-secret-preview-edit {
    border-left-color: #444444;
}

.hc-cloudtalk-agent-loader {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.hc-cloudtalk-agent-message {
    font-size: 12px;
    padding: 8px 10px;
    border-radius: 6px;
    background: #f8f9fa;
    color: #495057;
}

.hc-cloudtalk-agent-message.success {
    background: #e8f5e9;
    color: #2e7d32;
}

.hc-cloudtalk-agent-message.error {
    background: #ffebee;
    color: #c62828;
}

.hc-cloudtalk-sms-confirmation {
    padding: 10px;
    border: 1px solid #d6e4f0;
    border-radius: 6px;
    background: #f4f9ff;
    color: #333333;
    font-size: 12px;
}

.hc-cloudtalk-sms-confirmation > p {
    margin: 0 0 8px;
}

.hc-cloudtalk-sms-confirmation > p:last-child {
    margin: 8px 0 0;
}

.hc-cloudtalk-sms-confirmation > label {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    cursor: pointer;
}

.hc-cloudtalk-sms-confirmation input {
    flex-shrink: 0;
    margin: 3px 0 0;
}

.dark .hc-cloudtalk-sms-confirmation {
    background: #1e293b;
    border-color: #334155;
    color: #bbbbbb;
}

.hc-line-cloudtalk-webhook {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 8px 10px;
    margin-top: 5px;
    border: 1px solid #d6e4f0;
    border-radius: 6px;
    background: #f4f9ff;
}

.hc-line-cloudtalk-webhook-label {
    display: flex;
    align-items: center;
    gap: 6px;
    min-width: 0;
    font-size: 12px;
    font-weight: 600;
    color: #12a0f3;
}

.hc-line-cloudtalk-webhook-label span {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.hc-line-cloudtalk-webhook-button {
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
}

.dark .hc-line-cloudtalk-webhook {
    border-color: #334155;
    background: #1e293b;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import { OPEN_MODAL } from "@/actions/modal";
import lineService from "@/apis/project/line";
import KavkomDiagnostic from "./KavkomDiagnostic.vue";

export default {
    components: {
        KavkomDiagnostic,
    },

    props: {
        operator: {
            type: String,
            default: "",
        },

        config: {
            type: Object,
            default: () => ({}),
        },

        fields: {
            type: Array,
            default: () => [],
        },
        projectSlug: { type: String, default: "" },
        userId: {
            type: [Number, String],
            default: null,
        },
    },

    data() {
        return {
            cloudtalkAgents: [],
            cloudtalkMessage: "",
            cloudtalkMessageType: "",
            fetchingCloudtalkAgents: false,
            cloudtalkCredentialsVerified: false,
            cloudtalkCredentialsKey: null,
            cloudtalkAgentsRequestId: 0,
            autoFilledCloudtalkSmsNumber: "",
            confirmedCloudtalkSmsAgentId: "",
            editingFields: {},
            projects: [],
            sourceProjects: [],
            projectConfigMessage: "",
            loadingProjectConfig: false,
        };
    },

    mounted() {
        this.fetchInitialCloudtalkAgents();
        this.fetchAssignedProjects();
    },

    watch: {
        userId() {
            this.fetchAssignedProjects();
        },
    },

    beforeUnmount() {
        this.cloudtalkAgentsRequestId += 1;
    },

    methods: {
        toggleSourceProject(projectSlug, checked) {
            this.sourceProjects = checked
                ? [...this.sourceProjects, projectSlug]
                : this.sourceProjects.filter((slug) => slug !== projectSlug);
            this.loadProjectConfig();
        },
        async fetchAssignedProjects() {
            this.projects = [];
            this.sourceProjects = [];
            this.projectConfigMessage = "";
            if (!this.userId || !this.projectSlug) return;
            try {
                const { data } = await lineService.availableProjects(this.projectSlug, this.userId);
                this.projects = data.filter((project) => project.slug !== this.projectSlug);
            } catch (error) {
                this.projectConfigMessage = "Impossible de charger les projets affectés à cet utilisateur.";
            }
        },
        async loadProjectConfig() {
            this.projectConfigMessage = "";
            if (!this.sourceProjects.length) return;
            this.loadingProjectConfig = true;
            try {
                let loadedCount = 0;
                for (const projectSlug of this.sourceProjects) {
                    const { data: lines } = await lineService.get(projectSlug);
                    const source = lines.find((line) => line.operator === this.operator && line.user_id == this.userId);
                    if (!source) continue;
                    const { data: fullLine } = await lineService.show(projectSlug, source.id);
                    const reusable = { ...(fullLine.config ?? {}) };
                    delete reusable.agent_id;
                    Object.assign(this.config, reusable);
                    loadedCount += 1;
                }
                this.projectConfigMessage = loadedCount
                    ? `Configuration chargée depuis ${loadedCount} projet(s). En cas de doublon, le dernier projet sélectionné est prioritaire.`
                    : "Aucune configuration trouvée pour cet utilisateur dans les projets sélectionnés.";
            } catch (error) {
                this.projectConfigMessage = "Impossible de charger la configuration de ce projet.";
            } finally {
                this.loadingProjectConfig = false;
            }
        },
        confirmCloudtalkSmsNumber(checked) {
            if (!checked) {
                this.clearCloudtalkSmsConfirmation();
                return;
            }

            const number = this.cloudtalkAgentSmsNumber;
            if (!number) return;

            this.autoFilledCloudtalkSmsNumber = number;
            this.confirmedCloudtalkSmsAgentId = String(this.config.agent_id);
            this.config.numero = number;
        },

        clearCloudtalkSmsConfirmation(clearNumber = true) {
            if (
                clearNumber &&
                this.autoFilledCloudtalkSmsNumber &&
                this.config.numero === this.autoFilledCloudtalkSmsNumber
            ) {
                this.config.numero = "";
            }

            this.autoFilledCloudtalkSmsNumber = "";
            this.confirmedCloudtalkSmsAgentId = "";
        },

        fieldDisabled(field) {
            return (
                this.operator === "cloudtalk" &&
                field.key === "agent_id" &&
                (!this.cloudtalkCredentialsReady ||
                    this.fetchingCloudtalkAgents ||
                    this.cloudtalkAgents.length === 0)
            );
        },

        fieldRequired(field) {
            return field.required !== false;
        },

        fieldOptions(field) {
            if (this.operator === "cloudtalk" && field.key === "agent_id") {
                return this.cloudtalkAgents.map((agent) => ({
                    value: agent.id,
                    label: agent.label,
                }));
            }

            return field.options || [];
        },

        isSensitiveField(field) {
            return (
                field.type === "password" ||
                /(key|secret|token|sid)/i.test(field.key)
            );
        },

        hasFieldValue(field) {
            return (
                this.config[field.key] !== null &&
                this.config[field.key] !== undefined &&
                String(this.config[field.key]).length > 0
            );
        },

        maskedValue(field) {
            const value = String(this.config[field.key] ?? "");

            if (value.length <= 8) {
                return "******";
            }

            return value.slice(0, 4) + "******" + value.slice(-4);
        },

        editField(field) {
            this.editingFields = {
                ...this.editingFields,
                [field.key]: true,
            };
        },

        async fetchInitialCloudtalkAgents() {
            if (this.operator !== "cloudtalk" || !this.cloudtalkCredentialsReady) {
                return;
            }

            await this.fetchCloudtalkAgents(false);
        },

        async fetchCloudtalkAgents(showSuccessMessage = true) {
            if (this.operator !== "cloudtalk") return false;

            if (!this.cloudtalkCredentialsReady) {
                this.setCloudtalkError(
                    "Veuillez saisir l'identifiant et le secret de la clé API."
                );
                return false;
            }

            const requestId = ++this.cloudtalkAgentsRequestId;
            const credentialsKey = this.currentCloudtalkCredentialsKey;
            const isCurrentRequest = () => (
                requestId === this.cloudtalkAgentsRequestId &&
                this.operator === "cloudtalk" &&
                credentialsKey === this.currentCloudtalkCredentialsKey
            );

            this.fetchingCloudtalkAgents = true;
            this.cloudtalkMessage = "";
            this.cloudtalkMessageType = "";

            try {
                const { data } = await lineService.getCloudtalkAgents(
                    this.project.slug,
                    {
                        api_key_id: this.config.api_key_id,
                        api_key_secret: this.config.api_key_secret,
                    }
                );

                if (!isCurrentRequest()) return false;

                this.cloudtalkAgents = data.agents || [];
                this.cloudtalkCredentialsVerified = true;
                this.cloudtalkCredentialsKey = credentialsKey;

                if (
                    this.config.agent_id &&
                    !this.cloudtalkAgents.some(
                        (agent) => agent.id == this.config.agent_id
                    )
                ) {
                    this.clearCloudtalkSmsConfirmation();
                    this.config.agent_id = "";
                }

                if (
                    this.autoFilledCloudtalkSmsNumber &&
                    this.autoFilledCloudtalkSmsNumber !== this.cloudtalkAgentSmsNumber
                ) {
                    this.clearCloudtalkSmsConfirmation();
                }

                if (showSuccessMessage) {
                    this.cloudtalkMessage = this.cloudtalkAgents.length
                        ? this.cloudtalkAgents.length + " agent(s) chargé(s)."
                        : "Identifiants valides, mais aucun agent CloudTalk trouvé.";
                    this.cloudtalkMessageType = "success";
                }

                return true;
            } catch (error) {
                if (!isCurrentRequest()) return false;

                this.cloudtalkAgents = [];
                this.clearCloudtalkSmsConfirmation();
                this.config.agent_id = "";
                this.cloudtalkCredentialsVerified = false;
                this.cloudtalkCredentialsKey = null;
                this.setCloudtalkError(
                    error.response?.data?.message ||
                        "Impossible de vérifier les identifiants CloudTalk."
                );

                return false;
            } finally {
                if (requestId === this.cloudtalkAgentsRequestId) {
                    this.fetchingCloudtalkAgents = false;
                }
            }
        },

        async validate() {
            if (this.operator !== "cloudtalk") {
                return true;
            }

            if (!this.cloudtalkCredentialsReady) {
                this.setCloudtalkError(
                    "Veuillez saisir l'identifiant et le secret de la clé API."
                );
                return false;
            }

            if (
                !this.cloudtalkCredentialsVerified ||
                this.cloudtalkCredentialsKey !== this.currentCloudtalkCredentialsKey
            ) {
                const loaded = await this.fetchCloudtalkAgents(false);

                if (!loaded) {
                    return false;
                }
            }

            if (!this.config.agent_id) {
                this.setCloudtalkError("Veuillez sélectionner un agent CloudTalk.");
                return false;
            }

            return true;
        },

        resetCloudtalkAgents(clearAgent = true) {
            this.cloudtalkAgentsRequestId += 1;
            this.fetchingCloudtalkAgents = false;
            this.clearCloudtalkSmsConfirmation(clearAgent && this.operator === "cloudtalk");
            this.cloudtalkAgents = [];
            this.cloudtalkCredentialsVerified = false;
            this.cloudtalkCredentialsKey = null;
            this.cloudtalkMessage = "";
            this.cloudtalkMessageType = "";

            if (clearAgent && this.operator === "cloudtalk") {
                this.config.agent_id = "";
            }
        },

        setCloudtalkError(message) {
            this.cloudtalkMessage = message;
            this.cloudtalkMessageType = "error";
        },

        openCloudtalkWebhookModal() {
            store.commit(OPEN_MODAL, "setting-cloudtalk");
        },

        openCloudtalkApiHelp() {
            store.commit(OPEN_MODAL, "cloudtalk-api-help");
        },
    },

    watch: {
        operator() {
            this.clearCloudtalkSmsConfirmation(false);
            this.editingFields = {};
            this.resetCloudtalkAgents();
            this.fetchInitialCloudtalkAgents();
        },

        config() {
            this.editingFields = {};
            this.resetCloudtalkAgents(false);
            this.fetchInitialCloudtalkAgents();
        },

        "config.agent_id"(value, oldValue) {
            if (value !== oldValue) {
                this.clearCloudtalkSmsConfirmation();
            }
        },

        "config.numero"(value) {
            if (
                this.autoFilledCloudtalkSmsNumber &&
                value !== this.autoFilledCloudtalkSmsNumber
            ) {
                this.clearCloudtalkSmsConfirmation(false);
            }
        },

        "config.api_key_id"(value, oldValue) {
            if (value !== oldValue && oldValue !== undefined) {
                this.resetCloudtalkAgents();
            }
        },

        "config.api_key_secret"(value, oldValue) {
            if (value !== oldValue && oldValue !== undefined) {
                this.resetCloudtalkAgents();
            }
        },
    },

    computed: {
        ...mapGetters(["project"]),

        selectedCloudtalkAgent() {
            if (
                this.operator !== "cloudtalk" ||
                !this.cloudtalkCredentialsVerified ||
                this.cloudtalkCredentialsKey !== this.currentCloudtalkCredentialsKey
            ) {
                return null;
            }

            return this.cloudtalkAgents.find(
                (agent) => String(agent.id) === String(this.config.agent_id)
            ) || null;
        },

        cloudtalkAgentSmsNumber() {
            const number = this.selectedCloudtalkAgent?.default_number;
            return typeof number === "string" && /^\+[1-9]\d{1,14}$/.test(number)
                ? number
                : "";
        },

        cloudtalkSmsConfirmed() {
            return !!this.cloudtalkAgentSmsNumber &&
                this.autoFilledCloudtalkSmsNumber === this.cloudtalkAgentSmsNumber &&
                this.confirmedCloudtalkSmsAgentId === String(this.config.agent_id) &&
                this.config.numero === this.cloudtalkAgentSmsNumber;
        },

        cloudtalkCredentialsReady() {
            return !!this.config.api_key_id && !!this.config.api_key_secret;
        },

        currentCloudtalkCredentialsKey() {
            return [
                this.config.api_key_id || "",
                this.config.api_key_secret || "",
            ].join(":");
        },
    },
};
</script>
