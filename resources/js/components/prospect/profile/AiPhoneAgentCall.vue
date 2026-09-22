<template>
    <div class="hc-ai-phone-call">
        <select v-model="selectedAgentId" :disabled="loading || calling" @change="assignAgent">
            <option value="">Choisir un agent IA</option>
            <option v-for="agent in activeAgents" :key="agent.id" :value="String(agent.id)">
                {{ agent.name }}
            </option>
        </select>
        <button type="button" :disabled="!selectedAgentId || calling" @click="call">
            <i class="fa fa-phone"></i> {{ calling ? "Appel en cours..." : "Appeler avec l'IA" }}
        </button>
        <small v-if="message" :class="{ error: isError }">{{ message }}</small>
    </div>
</template>

<style scoped>
.hc-ai-phone-call { display: flex; align-items: center; gap: 8px; padding: 0 10px; }
.hc-ai-phone-call select { max-width: 180px; padding: 6px; border: 1px solid #d8dce3; border-radius: 4px; }
.hc-ai-phone-call button { border: 0; border-radius: 4px; padding: 7px 10px; color: #fff; background: #6b3fb5; cursor: pointer; white-space: nowrap; }
.hc-ai-phone-call button:disabled { opacity: .55; cursor: not-allowed; }
.hc-ai-phone-call small { color: #317a45; max-width: 240px; }
.hc-ai-phone-call small.error { color: #b42318; }
@media (max-width: 767px) { .hc-ai-phone-call { width: 100%; padding: 8px 14px; } .hc-ai-phone-call select { flex: 1; max-width: none; } }
</style>

<script>
import ApiService from "@/apis/api.service";
import AiAgentService from "@/apis/project/ai-agent";
import ProspectService from "@/apis/project/prospect";

export default {
    props: {
        project: { type: Object, required: true },
        prospect: { type: Object, required: true },
    },
    data() {
        return { agents: [], selectedAgentId: "", loading: false, calling: false, message: "", isError: false };
    },
    computed: {
        activeAgents() { return this.agents.filter((agent) => agent.is_active); },
    },
    created() { this.loadAgents(); },
    methods: {
        debug(event, details = {}) {
            // Diagnostic trace only: do not log SIP/API credentials here.
            window.console["info"](`[AI Phone] ${event}`, {
                projectId: this.project?.id,
                projectSlug: this.project?.slug,
                prospectId: this.prospect?.id,
                ...details,
            });
        },
        requestError(event, error) {
            window.console["error"](`[AI Phone] ${event}`, {
                projectId: this.project?.id,
                prospectId: this.prospect?.id,
                status: error?.response?.status,
                response: error?.response?.data,
                message: error?.message,
            });
        },
        async loadAgents() {
            this.loading = true;
            this.debug("Loading AI agents");
            try {
                const { data } = await AiAgentService.index(this.project.slug);
                this.agents = data;
                this.selectedAgentId = this.prospect.ai_agent_id ? String(this.prospect.ai_agent_id) : "";
                this.debug("AI agents loaded", {
                    agentCount: this.agents.length,
                    activeAgentIds: this.activeAgents.map((agent) => agent.id),
                    assignedAgentId: this.selectedAgentId || null,
                });
            } catch (error) {
                this.requestError("Unable to load AI agents", error);
                this.notice(error.response?.data?.message || "Impossible de charger les agents IA.", true);
            } finally {
                this.loading = false;
            }
        },
        async assignAgent() {
            this.message = "";
            this.debug("AI agent assignment requested", { agentId: this.selectedAgentId || null });
            try {
                await ProspectService.assignAiAgent(this.project.slug, this.prospect.id, this.selectedAgentId);
                this.prospect.ai_agent_id = this.selectedAgentId ? Number(this.selectedAgentId) : null;
                this.debug("AI agent assignment succeeded", { agentId: this.prospect.ai_agent_id });
                this.notice(this.selectedAgentId ? "Agent IA affecté au lead." : "Aucun agent IA affecté.");
            } catch (error) {
                this.requestError("Unable to assign AI agent", error);
                this.notice(error.response?.data?.message || "Impossible d’affecter cet agent IA.", true);
            }
        },
        async call() {
            if (!this.selectedAgentId) {
                this.debug("Call cancelled: no AI agent selected");
                return;
            }
            if (!window.confirm("Lancer l'appel vocal IA pour ce lead ?")) {
                this.debug("Call cancelled by user");
                return;
            }

            this.calling = true;
            this.message = "";
            this.debug("AI call request sent", { agentId: Number(this.selectedAgentId) });
            try {
                const { data } = await ApiService.post("settings/ai-phone-agent/call", {
                    prospect_id: this.prospect.id,
                    agent_id: Number(this.selectedAgentId),
                });
                this.debug("AI call request response", {
                    success: data?.success,
                    callUuid: data?.call_uuid || null,
                    message: data?.message || null,
                });
                this.notice(data.message || (data.success ? "Appel IA lancé : votre extension Kavkom doit sonner." : "L'appel IA n'a pas été lancé."), !data.success);
            } catch (error) {
                this.requestError("AI call request failed", error);
                this.notice(error.response?.data?.message || "Impossible de lancer l'appel IA.", true);
            } finally {
                this.calling = false;
                this.debug("AI call request finished");
            }
        },
        notice(message, isError = false) { this.message = message; this.isError = isError; },
    },
};
</script>
