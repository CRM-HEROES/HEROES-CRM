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

                <h3>Configuration Gemini</h3>
                <div class="hc-ai-agent-grid">
                    <label>Modèle<input v-model="form.config.model" placeholder="Modèle par défaut du service" /></label>
                    <label>Version API<input v-model="form.config.api_version" placeholder="v1alpha" /></label>
                </div>

                <h3>Configuration Kavkom</h3>
                <div class="hc-ai-agent-grid">
                    <label>Extension<input v-model="form.kavkom_config.extension" /></label>
                    <label>Domaine UUID<input v-model="form.kavkom_config.domain_uuid" /></label>
                    <label>Contexte SIP<input v-model="form.kavkom_config.user_context" /></label>
                    <label>Transport<select v-model="form.kavkom_config.transport"><option value="tls">TLS</option><option value="tcp">TCP</option><option value="udp">UDP</option></select></label>
                    <label>Port SIP<input v-model.number="form.kavkom_config.sip_port" type="number" min="1" max="65535" /></label>
                    <label>Token API<input v-model="form.kavkom_config.api_token" type="password" placeholder="Laisser vide pour conserver" /></label>
                    <label>Mot de passe SIP<input v-model="form.kavkom_config.password" type="password" placeholder="Laisser vide pour conserver" /></label>
                    <label>Refresh token<input v-model="form.kavkom_config.refresh_access_token" type="password" placeholder="Laisser vide pour conserver" /></label>
                </div>

                <div class="hc-ai-agent-actions">
                    <button v-if="form.id" type="button" class="hc-ai-agent-danger" @click="remove">Supprimer</button>
                    <span></span>
                    <button type="submit" class="hc-ai-agent-primary" :disabled="saving">
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
.hc-ai-agent-form input, .hc-ai-agent-form textarea { width: 100%; padding: 9px; border: 1px solid #d8dce3; border-radius: 4px; font: inherit; font-weight: 400; box-sizing: border-box; }
.hc-ai-agent-form textarea { resize: vertical; }
.hc-ai-agent-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
.hc-ai-agent-switch { flex-direction: row !important; align-items: center; font-weight: 400 !important; }
.hc-ai-agent-actions { margin-top: 8px; }
.hc-ai-agent-actions span { flex: 1; }
.hc-ai-agent-error { max-width: 1100px; margin: auto auto 16px; padding: 10px; color: #8a1c1c; background: #fde8e8; }
@media (max-width: 760px) { .hc-ai-agent-content { grid-template-columns: 1fr; } .hc-ai-agent-header { align-items: flex-start; } .hc-ai-agent-header p { display: none; } .hc-ai-agent-grid { grid-template-columns: 1fr; } }
</style>

<script>
import AiAgentService from "@/apis/project/ai-agent";

export default {
    data() {
        return { agents: [], selected: null, form: this.emptyForm(), loading: false, saving: false, error: "" };
    },
    created() {
        this.fetchAgents();
    },
    methods: {
        emptyForm() {
            return { id: null, name: "", is_active: true, script: "", instructions: "", config: { model: "", api_version: "" }, kavkom_config: { extension: "", domain_uuid: "", user_context: "", transport: "tls", sip_port: 5061, api_token: "", password: "", refresh_access_token: "" } };
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
            this.form = { ...this.emptyForm(), ...agent, config: { ...this.emptyForm().config, ...(agent.config || {}) }, kavkom_config: { ...this.emptyForm().kavkom_config, ...(agent.kavkom_config || {}) } };
            console.log("[AI Agent UI] Agent sélectionné", agent.id);
        },
        startCreate() { this.selected = null; this.form = this.emptyForm(); console.log("[AI Agent UI] Création d'un agent"); },
        async save() {
            this.saving = true;
            this.error = "";
            const payload = { ...this.form };
            delete payload.id;
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
};
</script>
