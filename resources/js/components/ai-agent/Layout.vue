<template>
    <div class="hc-ai-agent-page">
        <div class="hc-ai-agent-header">
            <button class="hc-ai-agent-back" @click="$router.back()">
                <i class="fa fa-arrow-left"></i>
            </button>
            <div>
                <h1>Agents IA</h1>
                <p>Créez et configurez les agents qui appelleront les prospects du projet.</p>
            </div>
            <button class="hc-ai-agent-primary" @click="startCreate">
                <i class="fa fa-plus"></i> Nouvel agent
            </button>
        </div>

        <div v-if="error" class="hc-ai-agent-error">{{ error }}</div>
        <loading :loading="loading" />

        <div class="hc-ai-agent-content">
            <aside class="hc-ai-agent-list">
                <button
                    v-for="agent in agents"
                    :key="agent.id"
                    class="hc-ai-agent-list-item"
                    :class="{ selected: selected && selected.id === agent.id }"
                    @click="selectAgent(agent)"
                >
                    <strong>{{ agent.name }}</strong>
                    <span>{{ agent.is_active ? "Actif" : "Inactif" }}</span>
                </button>
                <p v-if="!agents.length && !loading" class="hc-ai-agent-empty">
                    Aucun agent IA configuré.
                </p>
            </aside>

            <form class="hc-ai-agent-form" @submit.prevent="save">
                <div class="hc-ai-agent-form-header">
                    <h2>{{ form.id ? "Modifier l'agent" : "Créer un agent IA" }}</h2>
                    <label class="hc-ai-agent-switch">
                        <input type="checkbox" v-model="form.is_active" />
                        <span>Agent actif</span>
                    </label>
                </div>

                <label>Nom de l'agent <input v-model.trim="form.name" required maxlength="150" /></label>
                <label>Script / scénario<textarea v-model="form.script" rows="7" placeholder="Déroulement, étapes et questions de l'appel"></textarea></label>
                <label>Instructions comportementales<textarea v-model="form.instructions" rows="6" placeholder="Ton, langue, règles, informations à collecter"></textarea></label>

                <div class="hc-ai-agent-advanced">
                    <h3>Configuration Gemini</h3>
                    <div class="hc-ai-agent-grid">
                        <label>Clé API Gemini<input v-model="form.config.gemini_api_key" type="password" required placeholder="Clé Google AI Studio" /></label>
                        <label>Modèle Gemini Live<select v-model="form.config.gemini_live_model" required><option v-for="model in geminiLiveModels" :key="model" :value="model">{{ model }}</option></select></label>
                        <label>Modèle résumé<select v-model="form.config.gemini_summary_model" required><option v-for="model in geminiSummaryModels" :key="model" :value="model">{{ model }}</option></select></label>
                    </div>
                </div>

                <div class="hc-ai-agent-advanced">
                    <h3>Téléphone Kavkom de l'agent IA</h3>
                    <div class="hc-ai-agent-grid">
                        <label>Extension<input v-model.trim="form.kavkom_config.extension" required /></label>
                        <label>Mot de passe SIP<input v-model="form.kavkom_config.password" type="password" required placeholder="Mot de passe de l'extension" /></label>
                        <label>Numéro appelant<input v-model.trim="form.kavkom_config.caller_id_number" type="tel" required placeholder="33379580627" /></label>
                    </div>
                    <p v-if="phoneConflict" class="hc-ai-agent-warning">
                        Ce téléphone est déjà utilisé par l'agent IA "{{ phoneConflict.name }}".
                    </p>
                    <p v-if="extensionConflict" class="hc-ai-agent-warning">
                        Cette extension est déjà utilisée par l'agent IA "{{ extensionConflict.name }}".
                    </p>
                </div>
                <div class="hc-ai-agent-actions">
                    <button v-if="form.id" type="button" class="hc-ai-agent-danger" @click="remove">Supprimer</button>
                    <span></span>
                    <button type="submit" class="hc-ai-agent-primary" :disabled="saving || hasPhoneConflict">
                        <i class="fa fa-save"></i> {{ saving ? "Enregistrement..." : "Enregistrer" }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
.hc-ai-agent-page { padding: 24px; height: 100%; overflow: auto; background: #f5f6f8; }
.hc-ai-agent-header, .hc-ai-agent-form-header, .hc-ai-agent-actions { display: flex; align-items: center; gap: 16px; }
.hc-ai-agent-header { margin: 0 auto 20px; max-width: 1100px; }
.hc-ai-agent-header > div { flex: 1; }
.hc-ai-agent-header h1 { margin: 0; font-size: 22px; }
.hc-ai-agent-header p { margin: 6px 0 0; color: #6b7280; }
.hc-ai-agent-back, .hc-ai-agent-primary, .hc-ai-agent-danger, .hc-ai-agent-list-item { border: 0; cursor: pointer; }
.hc-ai-agent-back { background: transparent; font-size: 18px; }
.hc-ai-agent-primary { padding: 10px 14px; border-radius: 4px; color: #fff; background: #1e6ee5; }
.hc-ai-agent-danger { padding: 10px 14px; border-radius: 4px; color: #fff; background: #c0392b; }
.hc-ai-agent-content { display: grid; grid-template-columns: 280px minmax(0, 1fr); gap: 16px; max-width: 1100px; margin: auto; }
.hc-ai-agent-list, .hc-ai-agent-form { background: #fff; border: 1px solid #e4e7ec; border-radius: 6px; padding: 16px; }
.hc-ai-agent-list { align-self: start; padding: 8px; }
.hc-ai-agent-list-item { display: flex; flex-direction: column; align-items: flex-start; width: 100%; padding: 12px; background: transparent; text-align: left; border-radius: 4px; }
.hc-ai-agent-list-item.selected, .hc-ai-agent-list-item:hover { background: #edf4ff; }
.hc-ai-agent-list-item span { margin-top: 4px; color: #6b7280; font-size: 12px; }
.hc-ai-agent-empty { padding: 12px; color: #6b7280; font-size: 13px; }
.hc-ai-agent-form { display: flex; flex-direction: column; gap: 14px; }
.hc-ai-agent-form h2 { flex: 1; margin: 0; font-size: 18px; }
.hc-ai-agent-form h3 { margin: 8px 0 -4px; font-size: 14px; }
.hc-ai-agent-form label { display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600; }
.hc-ai-agent-form input, .hc-ai-agent-form textarea, .hc-ai-agent-form select { width: 100%; padding: 9px; border: 1px solid #d8dce3; border-radius: 4px; font: inherit; font-weight: 400; box-sizing: border-box; }
.hc-ai-agent-form textarea { resize: vertical; }
.hc-ai-agent-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
.hc-ai-agent-switch { flex-direction: row !important; align-items: center; font-weight: 400 !important; }
.hc-ai-agent-advanced { display: flex; flex-direction: column; gap: 12px; }
.hc-ai-agent-actions { margin-top: 8px; }
.hc-ai-agent-actions span { flex: 1; }
.hc-ai-agent-error { max-width: 1100px; margin: auto auto 16px; padding: 10px; color: #8a1c1c; background: #fde8e8; }
.hc-ai-agent-warning { margin: 0; padding: 10px; color: #8a5a00; background: #fff7d6; border-radius: 4px; font-size: 13px; }
@media (max-width: 760px) { .hc-ai-agent-content { grid-template-columns: 1fr; } .hc-ai-agent-header { align-items: flex-start; } .hc-ai-agent-header p { display: none; } .hc-ai-agent-grid { grid-template-columns: 1fr; } }
</style>

<script>
import AiAgentService from "@/apis/project/ai-agent";

export default {
    data() {
        return {
            agents: [],
            selected: null,
            form: this.emptyForm(),
            loading: false,
            saving: false,
            error: "",
            geminiLiveModels: ["models/gemini-2.5-flash-native-audio-preview-09-2025"],
            geminiSummaryModels: ["models/gemini-3.8-flash"],
        };
    },
    created() {
        this.fetchAgents();
    },
    methods: {
        emptyForm() {
            return {
                id: null,
                name: "",
                is_active: true,
                script: "",
                instructions: "",
                config: {
                    gemini_api_key: "",
                    gemini_live_model: "models/gemini-2.5-flash-native-audio-preview-09-2025",
                    gemini_summary_model: "models/gemini-3.8-flash",
                },
                kavkom_config: {
                    extension: "",
                    password: "",
                    caller_id_number: "",
                },
            };
        },
        async fetchAgents() {
            this.loading = true;
            this.error = "";
            console.log("[AI Agent UI] Chargement des agents du projet", this.$route.params.project);
            try {
                const { data } = await AiAgentService.index(this.$route.params.project);
                this.agents = data;
                if (data.length) this.selectAgent(data[0]);
            } catch (error) {
                console.error("[AI Agent UI] Échec du chargement", error);
                this.error = error.response?.data?.message || "Impossible de charger les agents IA.";
            } finally { this.loading = false; }
        },
        selectAgent(agent) {
            this.selected = agent;
            this.form = {
                ...this.emptyForm(),
                ...agent,
                config: this.normalizeConfig(agent.config || {}),
                kavkom_config: this.normalizeKavkomConfig(agent.kavkom_config || {}),
            };
            console.log("[AI Agent UI] Agent sélectionné", agent.id);
        },
        startCreate() { this.selected = null; this.form = this.emptyForm(); console.log("[AI Agent UI] Création d'un agent"); },
        normalizeConfig(config) {
            return {
                ...this.emptyForm().config,
                gemini_api_key: config.gemini_api_key || "",
                gemini_live_model: config.gemini_live_model || config.model || this.emptyForm().config.gemini_live_model,
                gemini_summary_model: config.gemini_summary_model || this.emptyForm().config.gemini_summary_model,
            };
        },
        normalizeKavkomConfig(config) {
            return {
                ...this.emptyForm().kavkom_config,
                extension: config.extension || "",
                password: config.password || "",
                caller_id_number: config.caller_id_number || config.phone_number || "",
            };
        },
        savePayload() {
            return {
                name: this.form.name,
                is_active: this.form.is_active,
                script: this.form.script,
                instructions: this.form.instructions,
                config: this.normalizeConfig(this.form.config),
                kavkom_config: this.normalizeKavkomConfig(this.form.kavkom_config),
            };
        },
        normalizeDigits(value) {
            return String(value || "").replace(/\D+/g, "");
        },
        async save() {
            this.saving = true;
            this.error = "";
            const payload = this.savePayload();
            console.log("[AI Agent UI] Enregistrement de l'agent", { name: payload.name, active: payload.is_active });
            try {
                const response = this.form.id ? await AiAgentService.update(this.$route.params.project, this.form.id, payload) : await AiAgentService.create(this.$route.params.project, payload);
                const agent = response.data;
                const index = this.agents.findIndex((item) => item.id === agent.id);
                if (index === -1) this.agents.push(agent); else this.agents.splice(index, 1, agent);
                this.selectAgent(agent);
                console.log("[AI Agent UI] Agent enregistré", agent.id);
            } catch (error) {
                console.error("[AI Agent UI] Échec de l'enregistrement", error);
                this.error = error.response?.data?.message || "Impossible d'enregistrer l'agent IA.";
            } finally { this.saving = false; }
        },
        async remove() {
            if (!window.confirm("Supprimer cet agent IA ?")) return;
            console.log("[AI Agent UI] Suppression de l'agent", this.form.id);
            await AiAgentService.destroy(this.$route.params.project, this.form.id);
            this.agents = this.agents.filter((agent) => agent.id !== this.form.id);
            this.startCreate();
        },
    },
    computed: {
        phoneConflict() {
            const phone = this.normalizeDigits(this.form.kavkom_config.caller_id_number);
            if (!phone) return null;

            return this.agents.find((agent) => {
                if (this.form.id && agent.id === this.form.id) return false;

                return this.normalizeDigits(agent.kavkom_config?.caller_id_number || agent.kavkom_config?.phone_number) === phone;
            }) || null;
        },
        extensionConflict() {
            const extension = String(this.form.kavkom_config.extension || "").trim().toLowerCase();
            if (!extension) return null;

            return this.agents.find((agent) => {
                if (this.form.id && agent.id === this.form.id) return false;

                return String(agent.kavkom_config?.extension || "").trim().toLowerCase() === extension;
            }) || null;
        },
        hasPhoneConflict() {
            return Boolean(this.phoneConflict || this.extensionConflict);
        },
    },
};
</script>
