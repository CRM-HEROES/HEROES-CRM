<template>
    <div class="hc-line-operator-config-fields">
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

        <kavkom-diagnostic v-if="operator === 'kavkom'" :config="config" />
    </div>
</template>

<style>
.hc-line-operator-config-fields {
    display: flex;
    flex-direction: column;
    gap: 5px;
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
</style>

<script>
import { mapGetters } from "vuex";
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
    },

    data() {
        return {
            cloudtalkAgents: [],
            cloudtalkMessage: "",
            cloudtalkMessageType: "",
            fetchingCloudtalkAgents: false,
            cloudtalkCredentialsVerified: false,
            cloudtalkCredentialsKey: null,
            editingFields: {},
        };
    },

    mounted() {
        this.fetchInitialCloudtalkAgents();
    },

    methods: {
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
            if (!this.cloudtalkCredentialsReady) {
                this.setCloudtalkError(
                    "Veuillez saisir l'identifiant et le secret de la clé API."
                );
                return false;
            }

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

                this.cloudtalkAgents = data.agents || [];
                this.cloudtalkCredentialsVerified = true;
                this.cloudtalkCredentialsKey = this.currentCloudtalkCredentialsKey;

                if (
                    this.config.agent_id &&
                    !this.cloudtalkAgents.some(
                        (agent) => agent.id == this.config.agent_id
                    )
                ) {
                    this.config.agent_id = "";
                }

                if (showSuccessMessage) {
                    this.cloudtalkMessage = this.cloudtalkAgents.length
                        ? this.cloudtalkAgents.length + " agent(s) chargé(s)."
                        : "Identifiants valides, mais aucun agent CloudTalk trouvé.";
                    this.cloudtalkMessageType = "success";
                }

                return true;
            } catch (error) {
                this.cloudtalkAgents = [];
                this.config.agent_id = "";
                this.cloudtalkCredentialsVerified = false;
                this.cloudtalkCredentialsKey = null;
                this.setCloudtalkError(
                    error.response?.data?.message ||
                        "Impossible de vérifier les identifiants CloudTalk."
                );

                return false;
            } finally {
                this.fetchingCloudtalkAgents = false;
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
    },

    watch: {
        operator() {
            this.editingFields = {};
            this.resetCloudtalkAgents();
            this.fetchInitialCloudtalkAgents();
        },

        config() {
            this.editingFields = {};
            this.resetCloudtalkAgents(false);
            this.fetchInitialCloudtalkAgents();
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
